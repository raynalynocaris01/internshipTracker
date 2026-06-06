<?php
namespace App\Core;

class Cache {
    private $cacheDir;
    private $ttl;
    
    public function __construct($ttl = 3600) {
        $this->cacheDir = __DIR__ . '/../../cache/';
        $this->ttl = $ttl;
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    public function get($key) {
        $file = $this->getFilename($key);
        
        if (file_exists($file) && (time() - filemtime($file)) < $this->ttl) {
            return unserialize(file_get_contents($file));
        }
        
        return null;
    }
    
    public function set($key, $data) {
        $file = $this->getFilename($key);
        return file_put_contents($file, serialize($data));
    }
    
    public function delete($key) {
        $file = $this->getFilename($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    public function clear() {
        $files = glob($this->cacheDir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
    
    private function getFilename($key) {
        return $this->cacheDir . md5($key) . '.cache';
    }
}