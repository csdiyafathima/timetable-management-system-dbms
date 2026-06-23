# 📅 Timetable Management System

A complete **Timetable Management System** mini project for **DBMS course** built with PHP, MySQL, HTML, and CSS.

## 🎯 Project Overview

This system allows administrators to efficiently manage courses, teachers, classrooms, and schedules with built-in conflict detection and a clean, responsive user interface.

## 🛠️ Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3
- **No frameworks** - Pure PHP for learning purposes

## 📋 Features

### ✅ Core Features
- **Complete CRUD Operations** for all entities
- **Conflict Detection** - Prevents double-booking of teachers and classrooms
- **Responsive Design** - Works on all devices
- **Input Validation** - Comprehensive error handling
- **Clean UI** - Modern and intuitive interface

### 📚 Entity Management
- **Courses** - Add, edit, delete courses with department information
- **Teachers** - Manage faculty with department assignments
- **Classrooms** - Configure room capacity and availability
- **Time Slots** - Define available time periods for scheduling
- **Timetable** - Schedule classes with automatic conflict checking

### 📊 Views & Reports
- **Dashboard** - System overview with statistics
- **Timetable View** - Complete weekly schedule display
- **Detailed Lists** - Comprehensive data tables
- **Print Support** - Printable timetable format

## 🗄️ Database Design

### Database: `timetable_db`

#### Tables:
1. **courses** (course_id, course_name, dept)
2. **teachers** (teacher_id, teacher_name, dept)
3. **classrooms** (room_id, room_number, capacity)
4. **timeslots** (slot_id, day, start_time, end_time)
5. **timetable** (tt_id, course_id, teacher_id, room_id, slot_id)

#### Relationships:
- Foreign key constraints ensure data integrity
- Unique constraints prevent conflicts
- Cascade deletes maintain referential integrity

## 🚀 Installation & Setup

### Prerequisites
- **XAMPP/WAMP/LAMP** or any PHP development environment
- **PHP 7.4+** with PDO MySQL extension
- **MySQL 5.7+** or **MariaDB 10.3+**
- **Web server** (Apache/Nginx)

### Step 1: Download Project
```bash
# Clone or download the project files
# Place them in your web server directory (htdocs for XAMPP)
```

### Step 2: Database Setup
1. **Start your MySQL server**
2. **Open phpMyAdmin** or MySQL command line
3. **Import the database schema**:
   ```sql
   -- Run the SQL commands from database/schema.sql
   -- This will create the database and tables with sample data
   ```

### Step 3: Configuration
1. **Open `config.php`**
2. **Update database credentials** if needed:
   ```php
   $host = 'localhost';
   $dbname = 'timetable_db';
   $username = 'root';        // Your MySQL username
   $password = '';            // Your MySQL password
   ```

### Step 4: Access the Application
1. **Start your web server**
2. **Navigate to**: `http://localhost/timtablemanagementsystem/`
3. **You should see the dashboard**

## 📁 Project Structure

```
timtablemanagementsystem/
├── css/
│   └── style.css              # Main stylesheet
├── database/
│   └── schema.sql             # Database schema and sample data
├── config.php                 # Database configuration
├── index.php                  # Dashboard/Home page
├── courses.php                # Course management
├── teachers.php               # Teacher management
├── classrooms.php             # Classroom management
├── timeslots.php              # Time slot management
├── timetable.php              # Timetable management
├── view_timetable.php         # Timetable view
└── README.md                  # This file
```

## 🎮 Usage Guide

### 1. Dashboard
- View system statistics
- Quick access to all features
- Overview of current data

### 2. Course Management
- Add new courses with department information
- Edit existing course details
- Delete courses (if not scheduled)

### 3. Teacher Management
- Add faculty members with department assignments
- Edit teacher information
- Delete teachers (only if no scheduled classes)

### 4. Classroom Management
- Add classrooms with capacity information
- Edit room details
- Delete classrooms (only if not in use)

### 5. Time Slot Management
- Define available time periods
- Set days and time ranges
- Prevent overlapping slots

### 6. Timetable Management
- Schedule classes by selecting:
  - Course
  - Teacher
  - Classroom
  - Time slot
- **Automatic conflict detection**:
  - Teacher double-booking prevention
  - Classroom double-booking prevention
- Edit or delete scheduled entries

### 7. View Timetable
- Complete weekly schedule view
- Statistics and summaries
- Print-friendly format
- Detailed class listings

## 🔧 Key Features Explained

### Conflict Detection
The system automatically prevents:
- **Teacher Conflicts**: Same teacher scheduled in multiple classes at the same time
- **Room Conflicts**: Same classroom booked for multiple classes simultaneously
- **Time Overlaps**: Overlapping time slots on the same day

### Input Validation
- Required field validation
- Time format validation
- Duplicate entry prevention
- Data sanitization for security

### Responsive Design
- Mobile-friendly interface
- Tablet and desktop optimized
- Print stylesheet included

## 🎨 Customization

### Styling
- Modify `css/style.css` for visual changes
- Color scheme can be updated in CSS variables
- Responsive breakpoints can be adjusted

### Database
- Add new fields to tables as needed
- Modify constraints and relationships
- Update sample data in `schema.sql`

### Functionality
- Add new features by extending existing pages
- Implement user authentication if needed
- Add export functionality for reports

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check MySQL server is running
   - Verify credentials in `config.php`
   - Ensure database exists

2. **Page Not Loading**
   - Check web server is running
   - Verify file permissions
   - Check PHP error logs

3. **Database Import Issues**
   - Run SQL commands manually
   - Check for syntax errors
   - Verify MySQL version compatibility

4. **Styling Issues**
   - Clear browser cache
   - Check CSS file path
   - Verify file permissions

## 📚 Learning Objectives

This project demonstrates:
- **Database Design** - Proper table relationships and constraints
- **PHP Programming** - CRUD operations and form handling
- **SQL Queries** - Complex joins and data manipulation
- **Web Development** - HTML forms and CSS styling
- **User Interface Design** - Responsive and user-friendly design
- **Error Handling** - Input validation and error messages

## 🤝 Contributing

This is an educational project. Feel free to:
- Add new features
- Improve the UI/UX
- Optimize database queries
- Add more validation rules
- Implement additional reports

## 📄 License

This project is created for educational purposes. Feel free to use and modify as needed.

## 👨‍💻 Author

Created as a **DBMS Course Project** demonstrating database management system concepts with practical implementation.

---

**Happy Learning! 🎓**
