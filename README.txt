============================================================
  SMART CLASSROOM MANAGEMENT SYSTEM
  College Project | PHP + MySQL + Bootstrap 5
============================================================

PROJECT TITLE:
  Smart Classroom Management Software for Enhanced Learning Environments

TECHNOLOGIES USED:
  - PHP 7.4+ (Backend)
  - MySQL (Database)
  - HTML5 + CSS3 (Structure & Styling)
  - Bootstrap 5.3 (Responsive UI Framework)
  - Bootstrap Icons (Icon Library)

------------------------------------------------------------
SETUP INSTRUCTIONS
------------------------------------------------------------

REQUIREMENTS:
  - XAMPP / WAMP / LAMP installed
  - PHP 7.4 or higher
  - MySQL 5.7 or higher
  - Browser (Chrome / Firefox recommended)

STEP 1 – Copy Project
  Copy the SmartClassroom folder to:
  → XAMPP: C:\xampp\htdocs\SmartClassroom
  → WAMP:  C:\wamp64\www\SmartClassroom

STEP 2 – Setup Database
  1. Start Apache and MySQL in XAMPP/WAMP
  2. Open browser and go to: http://localhost/phpmyadmin
  3. Click "New" and create database: smart_classroom
     (OR just import — it will create the DB automatically)
  4. Click "Import" → Choose file → Select "database.sql"
  5. Click "Go" to import

STEP 3 – Configure Database (if needed)
  Open config.php and update if your MySQL settings differ:
    define('DB_USER', 'root');   ← your MySQL username
    define('DB_PASS', '');       ← your MySQL password (blank for default XAMPP)

STEP 4 – Run the Project
  Open browser and go to:
  → http://localhost/SmartClassroom/

------------------------------------------------------------
LOGIN CREDENTIALS (All passwords: password)
------------------------------------------------------------

  ADMIN:
    Email:    admin@school.com
    Password: password

  TEACHER 1 (Prof. Rajesh Kumar):
    Email:    rajesh@school.com
    Password: password

  TEACHER 2 (Prof. Priya Sharma):
    Email:    priya@school.com
    Password: password

  STUDENT 1 (Amit Singh):
    Email:    amit@student.com
    Password: password

  STUDENT 2 (Priya Patel):
    Email:    ppriya@student.com
    Password: password

  STUDENT 3 (Rahul Verma):
    Email:    rahul@student.com
    Password: password

  STUDENT 4 (Sneha Gupta):
    Email:    sneha@student.com
    Password: password

  STUDENT 5 (Vikram Joshi):
    Email:    vikram@student.com
    Password: password

------------------------------------------------------------
FEATURES
------------------------------------------------------------

ADMIN:
  ✓ Dashboard with system statistics
  ✓ Manage Teachers (Add / Delete)
  ✓ Manage Students (Add / Delete / Assign Class)
  ✓ Manage Classes (Create / Delete)
  ✓ Timetable Management (Add / Delete / Filter by class)
  ✓ Reports (Attendance & Performance Analytics)

TEACHER:
  ✓ Dashboard (Class overview, quick actions)
  ✓ Mark Attendance (Daily, per class, with history)
  ✓ Manage Assignments (Post, View Submissions, Grade)
  ✓ Enter Marks (Subject-wise, Exam-type wise)
  ✓ Post Announcements (To specific classes)

STUDENT:
  ✓ Dashboard (Attendance summary, upcoming assignments)
  ✓ View Attendance (with percentage, warning if below 75%)
  ✓ View & Submit Assignments (with grade display)
  ✓ View Performance (Subject-wise marks, grades, charts)
  ✓ View Class Timetable (Today's classes highlighted)

------------------------------------------------------------
PROJECT STRUCTURE
------------------------------------------------------------

SmartClassroom/
├── index.php              ← Login Page
├── config.php             ← Database Configuration
├── logout.php             ← Logout Handler
├── database.sql           ← Database Schema + Demo Data
├── README.txt             ← This file
│
├── css/
│   └── style.css          ← Custom Styles
│
├── includes/
│   ├── auth.php           ← Authentication Functions
│   ├── header.php         ← Sidebar + Top Navigation
│   └── footer.php         ← Page Footer + Scripts
│
├── admin/
│   ├── dashboard.php      ← Admin Home
│   ├── manage_teachers.php
│   ├── manage_students.php
│   ├── manage_classes.php
│   ├── timetable.php
│   └── reports.php
│
├── teacher/
│   ├── dashboard.php      ← Teacher Home
│   ├── attendance.php     ← Mark Attendance
│   ├── assignments.php    ← Manage Assignments
│   ├── marks.php          ← Enter Marks
│   └── announcements.php  ← Post Announcements
│
├── student/
│   ├── dashboard.php      ← Student Home
│   ├── attendance.php     ← View Attendance
│   ├── assignments.php    ← View & Submit Assignments
│   ├── performance.php    ← View Marks & Grades
│   └── timetable.php      ← View Class Timetable
│
└── uploads/               ← File Uploads (future use)

------------------------------------------------------------
DEVELOPED BY: [Your Name]
COLLEGE:      [Your College Name]
DEPARTMENT:   [Your Department]
YEAR:         2026
------------------------------------------------------------
