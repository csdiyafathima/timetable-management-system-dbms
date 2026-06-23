-- Timetable Management System Database Schema
-- Database: timetable_db

CREATE DATABASE IF NOT EXISTS timetable_db;
USE timetable_db;

-- Table: courses
CREATE TABLE courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(100) NOT NULL,
    dept VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: teachers
CREATE TABLE teachers (
    teacher_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_name VARCHAR(100) NOT NULL,
    dept VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: classrooms
CREATE TABLE classrooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(20) NOT NULL UNIQUE,
    capacity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: timeslots
CREATE TABLE timeslots (
    slot_id INT AUTO_INCREMENT PRIMARY KEY,
    day VARCHAR(20) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: timetable
CREATE TABLE timetable (
    tt_id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    teacher_id INT NOT NULL,
    room_id INT NOT NULL,
    slot_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES classrooms(room_id) ON DELETE CASCADE,
    FOREIGN KEY (slot_id) REFERENCES timeslots(slot_id) ON DELETE CASCADE,
    UNIQUE KEY unique_teacher_slot (teacher_id, slot_id),
    UNIQUE KEY unique_room_slot (room_id, slot_id)
);

-- Table: users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample users
INSERT INTO users (username, password, role) VALUES
('admin', 'admin123', 'admin'),
('student1', 'student123', 'user'),
('Dr. John Smith', 'teacher123', 'user'),
('Prof. Jane Doe', 'teacher123', 'user'),
('Dr. Mike Johnson', 'teacher123', 'user');

-- Insert sample data
INSERT INTO courses (course_name, dept) VALUES
('Database Management Systems', 'Computer Science'),
('Data Structures', 'Computer Science'),
('Web Development', 'Computer Science'),
('Mathematics', 'Mathematics'),
('Physics', 'Physics');

INSERT INTO teachers (teacher_name, dept) VALUES
('Dr. John Smith', 'Computer Science'),
('Prof. Jane Doe', 'Computer Science'),
('Dr. Mike Johnson', 'Mathematics'),
('Prof. Sarah Wilson', 'Physics'),
('Dr. Robert Brown', 'Computer Science');

INSERT INTO classrooms (room_number, capacity) VALUES
('A101', 50),
('A102', 40),
('B201', 60),
('B202', 45),
('C301', 35);

INSERT INTO timeslots (day, start_time, end_time) VALUES
('Monday', '09:00:00', '10:30:00'),
('Monday', '10:45:00', '12:15:00'),
('Monday', '14:00:00', '15:30:00'),
('Tuesday', '09:00:00', '10:30:00'),
('Tuesday', '10:45:00', '12:15:00'),
('Wednesday', '09:00:00', '10:30:00'),
('Wednesday', '14:00:00', '15:30:00'),
('Thursday', '09:00:00', '10:30:00'),
('Thursday', '10:45:00', '12:15:00'),
('Friday', '09:00:00', '10:30:00');
