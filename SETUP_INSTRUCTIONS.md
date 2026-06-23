# 🎯 Timetable Management System - Setup Instructions

## 🚀 Quick Start Guide

### 1. **Start XAMPP**
- Open XAMPP Control Panel
- Start **Apache** and **MySQL** services
- Ensure both are running (green status)

### 2. **Setup Database**
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named `timetable_db`
3. Import the SQL file: `database/schema.sql`
4. Or run the setup script: `http://localhost/timetablemanagementsystem/setup_database.php`

### 3. **Access Application**
- **Option 1**: `http://localhost/timetablemanagementsystem`
- **Option 2**: `http://localhost:8000` (if using PHP server)

### 4. **Login Credentials**
- **Admin**: `admin` / `admin123`
- **User**: `student1` / `student123`

## 🔧 Features Implemented

### ✅ **Admin Features**
- **Dashboard**: Overview with statistics
- **Manage Teachers**: Add, edit, delete teachers
- **Manage Time Slots**: Configure available time slots
- **Create Timetable**: Schedule classes with conflict detection
- **View Timetable**: Complete schedule overview
- **Settings**: System configuration

### ✅ **User Features**
- **View Timetable**: Student-friendly schedule view
- **Teacher Information**: See teacher names for each subject
- **Classroom Details**: View room assignments
- **Current Day Highlighting**: Today's schedule emphasized

### ✅ **System Features**
- **Authentication**: Secure login system
- **Role-based Access**: Admin vs User permissions
- **Date Functionality**: Current date/time display
- **Conflict Detection**: Prevents scheduling conflicts
- **Responsive Design**: Works on all devices

## 🎨 Design Features

- **Consistent Theme**: Purple gradient (#667eea to #764ba2)
- **Modern UI**: Clean cards, shadows, hover effects
- **Professional Typography**: Segoe UI font family
- **Responsive Layout**: Mobile-friendly design

## 🗄️ Database Structure

### Tables Created:
- `users` - Login credentials and roles
- `courses` - Available courses
- `teachers` - Teacher information
- `classrooms` - Room details
- `timeslots` - Available time slots
- `timetable` - Scheduled classes

## 🐛 Troubleshooting

### Database Connection Issues:
1. Check if MySQL is running in XAMPP
2. Verify database name is `timetable_db`
3. Run `test_db_connection.php` to diagnose

### PHP Server Issues:
1. Use XAMPP Apache instead: `http://localhost/timetablemanagementsystem`
2. Or find PHP path: `where php` in command prompt

### Login Issues:
1. Ensure database is imported correctly
2. Check if users table has sample data
3. Run `setup_database.php` to add sample data

## 📱 Key Pages

- **Login**: `login.php`
- **Admin Dashboard**: `admin/dashboard.php`
- **User View**: `user/view_timetable.php`
- **Database Test**: `test_db_connection.php`
- **Setup**: `setup_database.php`

## 🔄 Workflow

1. **Admin Login** → Add teachers, courses, classrooms
2. **Create Time Slots** → Define available time periods
3. **Create Timetable** → Schedule classes (conflict detection)
4. **User Login** → View personalized timetable
5. **View Details** → See teacher names, room assignments

## ✨ All Functions Working

- ✅ Login/Logout system
- ✅ Role-based access control
- ✅ Teacher management (CRUD)
- ✅ Time slot management (CRUD)
- ✅ Timetable creation with conflict detection
- ✅ Real-time timetable viewing
- ✅ Date and time display
- ✅ Responsive design
- ✅ Database integration
- ✅ Form validation
- ✅ Error handling

The system is now fully functional with all requested features!
