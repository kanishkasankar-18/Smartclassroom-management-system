-- ============================================================
-- Smart Classroom Management System
-- Database: smart_classroom
-- Run this file in phpMyAdmin or MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS smart_classroom;
USE smart_classroom;

-- Users Table (Admin, Teachers, Students)
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(100) UNIQUE NOT NULL,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('admin','teacher','student') NOT NULL,
    phone       VARCHAR(20),
    address     TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Classes Table
CREATE TABLE IF NOT EXISTS classes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    section     VARCHAR(10),
    teacher_id  INT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Student-Class Mapping
CREATE TABLE IF NOT EXISTS student_classes (
    student_id  INT,
    class_id    INT,
    PRIMARY KEY (student_id, class_id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id)   REFERENCES classes(id) ON DELETE CASCADE
);

-- Attendance Table
CREATE TABLE IF NOT EXISTS attendance (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    student_id  INT,
    class_id    INT,
    date        DATE NOT NULL,
    status      ENUM('present','absent','late') DEFAULT 'absent',
    marked_by   INT,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (class_id)   REFERENCES classes(id),
    FOREIGN KEY (marked_by)  REFERENCES users(id)
);

-- Assignments Table
CREATE TABLE IF NOT EXISTS assignments (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    description TEXT,
    class_id    INT,
    teacher_id  INT,
    due_date    DATE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id)   REFERENCES classes(id),
    FOREIGN KEY (teacher_id) REFERENCES users(id)
);

-- Assignment Submissions
CREATE TABLE IF NOT EXISTS submissions (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id   INT,
    student_id      INT,
    submitted_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    grade           VARCHAR(10),
    feedback        TEXT,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id),
    FOREIGN KEY (student_id)    REFERENCES users(id)
);

-- Marks Table
CREATE TABLE IF NOT EXISTS marks (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    student_id      INT,
    subject         VARCHAR(100),
    class_id        INT,
    exam_type       ENUM('Unit Test 1','Unit Test 2','Mid Term','Final Exam','Assignment') DEFAULT 'Unit Test 1',
    marks_obtained  DECIMAL(5,2),
    max_marks       DECIMAL(5,2) DEFAULT 100,
    entered_by      INT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (class_id)   REFERENCES classes(id),
    FOREIGN KEY (entered_by) REFERENCES users(id)
);

-- Announcements Table
CREATE TABLE IF NOT EXISTS announcements (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    message     TEXT NOT NULL,
    teacher_id  INT,
    class_id    INT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id),
    FOREIGN KEY (class_id)   REFERENCES classes(id)
);

-- Timetable Table
CREATE TABLE IF NOT EXISTS timetable (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    class_id    INT,
    subject     VARCHAR(100),
    teacher_id  INT,
    day         ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
    start_time  TIME NOT NULL,
    end_time    TIME NOT NULL,
    FOREIGN KEY (class_id)   REFERENCES classes(id),
    FOREIGN KEY (teacher_id) REFERENCES users(id)
);

-- ============================================================
-- DEMO DATA (Password for ALL users: password)
-- ============================================================

INSERT INTO users (name, email, password, role, phone) VALUES
('Administrator',    'admin@school.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',   '9876540001'),
('Prof. Rajesh Kumar','rajesh@school.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', '9876540002'),
('Prof. Priya Sharma','priya@school.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', '9876540003'),
('Amit Singh',       'amit@student.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '9876540004'),
('Priya Patel',      'ppriya@student.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '9876540005'),
('Rahul Verma',      'rahul@student.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '9876540006'),
('Sneha Gupta',      'sneha@student.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '9876540007'),
('Vikram Joshi',     'vikram@student.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '9876540008');

INSERT INTO classes (name, section, teacher_id) VALUES
('Computer Science', 'A', 2),
('Information Technology', 'B', 3);

INSERT INTO student_classes (student_id, class_id) VALUES
(4, 1), (5, 1), (6, 1),
(7, 2), (8, 2);

INSERT INTO timetable (class_id, subject, teacher_id, day, start_time, end_time) VALUES
(1, 'Data Structures',      2, 'Monday',    '09:00:00', '10:00:00'),
(1, 'Database Management',  2, 'Monday',    '10:00:00', '11:00:00'),
(1, 'Web Technology',       2, 'Tuesday',   '09:00:00', '10:00:00'),
(1, 'Computer Networks',    2, 'Wednesday', '09:00:00', '10:00:00'),
(1, 'Operating Systems',    2, 'Thursday',  '09:00:00', '10:00:00'),
(1, 'Data Structures',      2, 'Friday',    '09:00:00', '10:00:00'),
(2, 'Software Engineering', 3, 'Monday',    '11:00:00', '12:00:00'),
(2, 'Information Security', 3, 'Tuesday',   '11:00:00', '12:00:00'),
(2, 'Cloud Computing',      3, 'Wednesday', '11:00:00', '12:00:00'),
(2, 'Software Engineering', 3, 'Thursday',  '11:00:00', '12:00:00');

INSERT INTO attendance (student_id, class_id, date, status, marked_by) VALUES
(4, 1, '2026-06-23', 'present', 2), (5, 1, '2026-06-23', 'present', 2), (6, 1, '2026-06-23', 'absent', 2),
(4, 1, '2026-06-24', 'present', 2), (5, 1, '2026-06-24', 'absent',  2), (6, 1, '2026-06-24', 'present', 2),
(4, 1, '2026-06-25', 'present', 2), (5, 1, '2026-06-25', 'present', 2), (6, 1, '2026-06-25', 'late',    2),
(4, 1, '2026-06-26', 'present', 2), (5, 1, '2026-06-26', 'absent',  2), (6, 1, '2026-06-26', 'present', 2),
(7, 2, '2026-06-23', 'present', 3), (8, 2, '2026-06-23', 'present', 3),
(7, 2, '2026-06-24', 'absent',  3), (8, 2, '2026-06-24', 'present', 3),
(7, 2, '2026-06-25', 'present', 3), (8, 2, '2026-06-25', 'late',    3);

INSERT INTO assignments (title, description, class_id, teacher_id, due_date) VALUES
('Data Structures Assignment 1',
 'Implement a Binary Search Tree (BST) with insert, delete, search, and traversal operations in C/C++/Java. Write a 2-page report explaining time complexity.',
 1, 2, '2026-07-10'),
('Database Design Project',
 'Design a complete ER diagram for a Hospital Management System. Include all entities, relationships, cardinalities, and attributes. Submit PDF.',
 1, 2, '2026-07-15'),
('Web Technology Mini Project',
 'Create a responsive website with HTML, CSS, and JavaScript for any topic of your choice. Must include navigation, images, forms, and contact page.',
 1, 2, '2026-07-20'),
('Software Engineering Case Study',
 'Analyze the SDLC of any real-world software product (e.g., Zomato, Paytm). Write a 10-page report covering requirements, design, testing, and maintenance phases.',
 2, 3, '2026-07-12'),
('Information Security Report',
 'Write a detailed report on any cybersecurity attack (SQL Injection, XSS, Phishing, etc.). Explain how it works, real-world examples, and preventive measures.',
 2, 3, '2026-07-18');

INSERT INTO submissions (assignment_id, student_id, grade, feedback) VALUES
(1, 4, 'A', 'Excellent implementation! Clean code and well-documented.'),
(1, 5, 'B+', 'Good work, minor issues with deletion logic.'),
(4, 7, 'A+', 'Outstanding analysis with detailed diagrams.');

INSERT INTO marks (student_id, subject, class_id, exam_type, marks_obtained, max_marks, entered_by) VALUES
(4, 'Data Structures',     1, 'Unit Test 1', 85,  100, 2),
(4, 'Database Management', 1, 'Unit Test 1', 92,  100, 2),
(4, 'Web Technology',      1, 'Unit Test 1', 78,  100, 2),
(4, 'Data Structures',     1, 'Mid Term',    88,  100, 2),
(5, 'Data Structures',     1, 'Unit Test 1', 76,  100, 2),
(5, 'Database Management', 1, 'Unit Test 1', 88,  100, 2),
(5, 'Web Technology',      1, 'Unit Test 1', 95,  100, 2),
(5, 'Data Structures',     1, 'Mid Term',    72,  100, 2),
(6, 'Data Structures',     1, 'Unit Test 1', 65,  100, 2),
(6, 'Database Management', 1, 'Unit Test 1', 72,  100, 2),
(6, 'Web Technology',      1, 'Unit Test 1', 80,  100, 2),
(7, 'Software Engineering',2, 'Unit Test 1', 88,  100, 3),
(7, 'Information Security',2, 'Unit Test 1', 75,  100, 3),
(7, 'Software Engineering',2, 'Mid Term',    91,  100, 3),
(8, 'Software Engineering',2, 'Unit Test 1', 91,  100, 3),
(8, 'Information Security',2, 'Unit Test 1', 83,  100, 3);

INSERT INTO announcements (title, message, teacher_id, class_id) VALUES
('Mid-Term Exam Schedule Released',
 'Mid-term exams will be held from July 15 to July 20, 2026. The exam will cover all topics from Unit 1 and Unit 2. Timetable has been uploaded on the notice board.',
 2, 1),
('Assignment Submission Reminder',
 'This is a reminder that all pending assignments must be submitted before the due date. Late submissions will NOT be accepted under any circumstances.',
 2, 1),
('Project Presentation Schedule',
 'Project presentations for Software Engineering will be held on July 25, 2026. All groups must be ready with their demos and documentation. Dress code: Formal.',
 3, 2),
('Holiday Notice',
 'The college will remain closed on July 3, 2026 (Thursday) on account of a local festival. Classes will resume on July 4 (Friday) as usual.',
 3, 2);
