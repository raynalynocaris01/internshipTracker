<?php
namespace App\Controllers;

class QRController extends BaseController {
    public function generate() {
        $this->requireTeacher();
        $this->ensureMethod('POST');
        $sectionId = $this->sanitizeInt($_POST['section_id'] ?? 0);
        $date = $this->sanitizeString($_POST['date'] ?? date('Y-m-d'));
        $sessionType = $this->sanitizeString($_POST['session_type'] ?? 'morning_in');

        if ($sectionId <= 0 || !$date || !$sessionType) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing QR parameters'], 400);
        }

        $token = bin2hex(random_bytes(8));
        $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        try {
            $stmt = $this->pdo->prepare("INSERT INTO qr_sessions (qr_token, section_id, date, session_type, expires_at) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$token, $sectionId, $date, $sessionType, $expires]);
        } catch (\Exception $e) {
            // If table does not exist, continue with a transient token response.
        }

        $this->jsonResponse(['success' => true, 'token' => $token, 'expires_at' => $expires]);
    }

    public function verify($token = null) {
        $this->requireStudent();
        if (!$token) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing token'], 400);
        }

        $stmt = $this->pdo->prepare("SELECT * FROM qr_sessions WHERE qr_token = ? LIMIT 1");
        $found = false;
        try {
            $stmt->execute([$token]);
            $found = $stmt->fetch();
        } catch (\Exception $e) {
            $found = false;
        }

        if (!$found) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid QR token'], 404);
        }

        $this->jsonResponse(['success' => true, 'qr_session' => $found]);
    }

    public function active() {
        $this->requireTeacher();
        $sectionId = $this->sanitizeInt($_GET['section_id'] ?? 0);
        if ($sectionId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing section_id'], 400);
        }

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM qr_sessions WHERE section_id = ? AND expires_at >= NOW() ORDER BY id DESC");
            $stmt->execute([$sectionId]);
            $qrSessions = $stmt->fetchAll();
        } catch (\Exception $e) {
            $qrSessions = [];
        }

        $this->jsonResponse(['success' => true, 'qr_codes' => $qrSessions]);
    }

    public function show() {
        $this->requireTeacher();
        $token = $this->sanitizeString($_GET['token'] ?? '');
        if (!$token) {
            echo '<p>QR token missing.</p>';
            return;
        }
        echo '<div style="font-family:Arial,sans-serif;text-align:center;padding:40px;">';
        echo '<h1>QR Token</h1>';
        echo '<p><strong>' . htmlspecialchars($token) . '</strong></p>';
        echo '<p>Use this token for attendance.</p>';
        echo '</div>';
    }

    public function deactivate() {
        $this->requireTeacher();
        $this->ensureMethod('POST');
        $qrId = $this->sanitizeInt($_POST['qr_id'] ?? 0);
        if ($qrId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid QR id'], 400);
        }

        try {
            $stmt = $this->pdo->prepare("UPDATE qr_sessions SET expires_at = NOW() WHERE id = ?");
            $stmt->execute([$qrId]);
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Unable to deactivate QR code'] , 500);
        }
    }
}
