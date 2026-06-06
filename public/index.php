<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;

session_start();

$router = new Router();

// Auth Routes
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// Admin Routes
$router->get('/admin/dashboard', 'AdminController@dashboard');
$router->get('/admin/instructors', 'AdminController@instructors');
$router->post('/admin/instructors/add', 'AdminController@addInstructor');
$router->post('/admin/instructors/edit', 'AdminController@editInstructor');
$router->get('/admin/instructors/delete/{id}', 'AdminController@deleteInstructor');
$router->get('/admin/subjects', 'AdminController@subjects');
$router->post('/admin/subjects/add', 'AdminController@addSubject');
$router->get('/admin/sections', 'AdminController@sections');
$router->get('/admin/students', 'AdminController@students');

// Teacher Routes
$router->get('/teacher/dashboard', 'TeacherController@dashboard');
$router->get('/teacher/subjects', 'TeacherController@subjects');
$router->post('/teacher/subjects/edit', 'TeacherController@editSubject');
$router->get('/teacher/sections', 'TeacherController@sections');
$router->get('/teacher/students', 'TeacherController@students');
$router->post('/teacher/students/{sectionId}/add', 'TeacherController@addStudent');
$router->post('/teacher/students/{sectionId}/edit', 'TeacherController@editStudent');
$router->get('/teacher/students/{sectionId}/delete/{id}', 'TeacherController@deleteStudent');
$router->post('/teacher/students/{sectionId}/edit-username', 'TeacherController@editUsername');
$router->post('/teacher/students/{sectionId}/edit-password', 'TeacherController@editPassword');
$router->get('/teacher/students/{sectionId}/remove-login/{id}', 'TeacherController@removeLogin');
$router->get('/teacher/students/{sectionId}/create-login/{id}', 'TeacherController@createLogin');
$router->get('/teacher/attendance', 'TeacherController@attendance');
$router->get('/teacher/attendance/load', 'TeacherController@loadAttendance');
$router->post('/teacher/attendance/save', 'TeacherController@saveAttendance');

// Student Routes
$router->get('/student/dashboard', 'StudentController@dashboard');
$router->get('/student/qr-attendance', 'StudentController@qrAttendance');
$router->post('/student/record-attendance', 'StudentController@recordAttendance');

// QR Routes
$router->post('/qr/generate', 'QRController@generate');
$router->get('/qr/verify/{token}', 'QRController@verify');
$router->get('/qr/show', 'QRController@show');
$router->get('/qr/active', 'QRController@active');
$router->post('/qr/deactivate', 'QRController@deactivate');

// API Routes
$router->get('/api/subjects', 'ApiController@getSubjects');
$router->post('/api/attendance', 'ApiController@saveAttendance');
$router->get('/api/students', 'ApiController@getStudents');

$router->dispatch();