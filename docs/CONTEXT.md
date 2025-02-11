# Primary School Result Management System

## Overview
This document outlines the system flow and features of a Primary School Result Management System designed for schools in Cameroon. The system manages academic results, calculates averages, ranks pupils, generates report cards, and handles automatic promotions.

## Core Features

### Admin Section
- Manage pupils and teachers
- Configure system settings
- Generate and distribute report cards
- View comprehensive academic data
- Handle promotions and graduations

### Teacher Section
- Record student results per term
- View class rankings and performance
- Input sequence and exam marks
- Access term-specific calculations

## Detailed System Components

### 1. Teacher's Section

#### 1.1 Authentication
- Login using unique matricule and 8-digit PIN
- Class-specific access restrictions

#### 1.2 Result Management
- Term selection (first, second, third)
- Mark entry for:
  - First Sequence
  - Second Sequence
  - Exams
- Automated calculations for:
  - Total marks
  - Term averages
  - Class rankings

### 2. Admin Section

#### 2.1 User Management
- Create teacher accounts (generates matricule and PIN)
- Register pupils with unique IDs
- Manage subject listings
- Two admin levels:
  - Super Admin (full access)
  - Sub Admin (limited access)

#### 2.2 Academic Management
- View comprehensive results
- Generate report cards
- Share results with parents
- Configure term schedules
- Set result recording periods

### 3. System Logic

#### 3.1 Calculations
- Term averages
- Yearly averages
- Class rankings
- Promotion criteria evaluation

#### 3.2 Automatic Processing
- Class promotions
- Primary 6 graduations
- Report card generation

## Database Structure

### Core Tables

#### Teachers
| Field | Type | Constraints | Description |
|-------|------|------------|-------------|
| teacher_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| matricule | VARCHAR(10) | UNIQUE, NOT NULL | Teacher's matricule |
| pin | CHAR(8) | NOT NULL | 8-digit PIN |
| name | VARCHAR(100) | NOT NULL | Full name |
| email | VARCHAR(100) | UNIQUE | Email address |
| phone | VARCHAR(15) | | Contact number |
| assigned_class | VARCHAR(20) | NOT NULL | Current class |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation date |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Last update |
| status | ENUM | ('active','inactive') | Account status |

#### Pupils
| Field | Type | Constraints | Description |
|-------|------|------------|-------------|
| pupil_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| matricule | VARCHAR(10) | UNIQUE, NOT NULL | Student's matricule |
| first_name | VARCHAR(50) | NOT NULL | First name |
| last_name | VARCHAR(50) | NOT NULL | Last name |
| date_of_birth | DATE | NOT NULL | Birth date |
| gender | ENUM | ('M','F') | Gender |
| parent_details | JSON | NOT NULL | Parent contact info |
| class | VARCHAR(20) | NOT NULL | Current class |
| admission_date | DATE | NOT NULL | Date admitted |
| status | ENUM | ('active','inactive','graduated') | Current status |

#### Results
| Field | Type | Constraints | Description |
|-------|------|------------|-------------|
| result_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| pupil_id | INT | FOREIGN KEY | Reference to pupil |
| subject_id | INT | FOREIGN KEY | Reference to subject |
| academic_year | VARCHAR(9) | NOT NULL | Academic year |
| term | TINYINT | NOT NULL | Term (1,2,3) |
| first_sequence_marks | DECIMAL(5,2) | DEFAULT NULL | First sequence |
| second_sequence_marks | DECIMAL(5,2) | DEFAULT NULL | Second sequence |
| exam_marks | DECIMAL(5,2) | DEFAULT NULL | Exam marks |
| total_marks | DECIMAL(5,2) | DEFAULT NULL | Calculated total |
| term_average | DECIMAL(5,2) | DEFAULT NULL | Term average |
| ranking | INT | DEFAULT NULL | Class position |
| teacher_comment | TEXT | | Teacher remarks |

#### Subjects
| Field | Type | Constraints | Description |
|-------|------|------------|-------------|
| subject_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| name | VARCHAR(50) | NOT NULL | Subject name |
| code | VARCHAR(10) | UNIQUE | Subject code |
| class | VARCHAR(20) | NOT NULL | Associated class |
| coefficient | DECIMAL(3,1) | NOT NULL DEFAULT 1.0 | Subject weight |
| category | VARCHAR(50) | | Subject category |

#### Admins
| Field | Type | Constraints | Description |
|-------|------|------------|-------------|
| admin_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| username | VARCHAR(50) | UNIQUE, NOT NULL | Login username |
| password | VARCHAR(255) | NOT NULL | Hashed password |
| email | VARCHAR(100) | UNIQUE | Email address |
| role | ENUM | ('super_admin','sub_admin') | Admin level |
| status | ENUM | ('active','inactive') | Account status |
| last_login | TIMESTAMP | | Last login time |

### Supporting Tables

#### Attendance
| Field | Type | Constraints | Description |
|-------|------|------------|-------------|
| attendance_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| pupil_id | INT | FOREIGN KEY | Reference to pupil |
| date | DATE | NOT NULL | Attendance date |
| status | ENUM | ('present','absent','late') | Status |
| term | TINYINT | NOT NULL | Term number |
| reason | TEXT | | Absence reason |

#### Parent_Accounts
| Field | Type | Constraints | Description |
|-------|------|------------|-------------|
| parent_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| username | VARCHAR(50) | UNIQUE, NOT NULL | Login username |
| password | VARCHAR(255) | NOT NULL | Hashed password |
| email | VARCHAR(100) | UNIQUE | Email address |
| phone | VARCHAR(15) | NOT NULL | Contact number |
| pupils | JSON | NOT NULL | Array of pupil_ids |
| status | ENUM | ('active','inactive') | Account status |

## Technical Stack

### Frontend
- HTML/CSS/ tailwind css
- JavaScript

### Backend
- php

### Database
- MYSQL

### Additional Tools
- JWT/OAuth for authentication
- PDF generation libraries

## Additional System Components

### Security Measures
- Password hashing and encryption
- Role-based access control (RBAC)
- Session management and timeout
- Audit logging for sensitive operations
- Regular backup scheduling

### Reporting Features
- Customizable report card templates
- Statistical analysis tools
  - Class performance trends
  - Subject-wise analysis
  - Year-over-year comparisons
- Bulk report generation
- Export options (PDF, Excel)

### Parent Portal
- Secure login for parents
- View child's academic progress
- Download report cards
- Communication with teachers
- Attendance tracking

### Data Validation Rules
- Grade ranges (0-20 for Cameroon system)
- Attendance thresholds
- Promotion criteria
  - Minimum average score
  - Required subjects passing grade
- Maximum class size limits

### Additional Database Tables

#### Audit_Logs
| Field | Description |
|-------|-------------|
| log_id | Primary Key |
| user_id | Foreign Key |
| action | Action performed |
| table_affected | Database table |
| timestamp | Time of action |
| ip_address | User's IP |

#### System_Settings
| Field | Description |
|-------|-------------|
| setting_id | Primary Key |
| key | Setting name |
| value | Setting value |
| description | Setting description |
| last_updated | Last modified date |

## Complete Database Schema (SQL)

### Core Tables

#### Teachers
| Field | Type | Constraints | Description |
|-------|------|------------|-------------|
| teacher_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| matricule | VARCHAR(10) | UNIQUE, NOT NULL | Teacher's matricule |
| pin | CHAR(8) | NOT NULL | 8-digit PIN |
| name | VARCHAR(100) | NOT NULL | Full name |
| email | VARCHAR(100) | UNIQUE | Email address |
| phone | VARCHAR(15) | | Contact number |
| assigned_class | VARCHAR(20) | NOT NULL | Current class |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation date |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Last update |
| status | ENUM | ('active','inactive') | Account status |

#### Pupils
| Field | Type | Constraints | Description |
|-------|------|------------|-------------|
| pupil_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| matricule | VARCHAR(10) | UNIQUE, NOT NULL | Student's matricule |
| first_name | VARCHAR(50) | NOT NULL | First name |
| last_name | VARCHAR(50) | NOT NULL | Last name |
| date_of_birth | DATE | NOT NULL | Birth date |
| gender | ENUM | ('M','F') | Gender |
| parent_details | JSON | NOT NULL | Parent contact info |
| class | VARCHAR(20) | NOT NULL | Current class |
| admission_date | DATE | NOT NULL | Date admitted |
| status | ENUM | ('active','inactive','graduated') | Current status |

#### Results
| Field | Type | Constraints | Description |
|-------|------|------------|-------------|
| result_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| pupil_id | INT | FOREIGN KEY | Reference to pupil |
| subject_id | INT | FOREIGN KEY | Reference to subject |
| academic_year | VARCHAR(9) | NOT NULL | Academic year |
| term | TINYINT | NOT NULL | Term (1,2,3) |
| first_sequence_marks | DECIMAL(5,2) | DEFAULT NULL | First sequence |
| second_sequence_marks | DECIMAL(5,2) | DEFAULT NULL | Second sequence |
| exam_marks | DECIMAL(5,2) | DEFAULT NULL | Exam marks |
| total_marks | DECIMAL(5,2) | DEFAULT NULL | Calculated total |
| term_average | DECIMAL(5,2) | DEFAULT NULL | Term average |
| ranking | INT | DEFAULT NULL | Class position |
| teacher_comment | TEXT | | Teacher remarks |

#### Subjects
| Field | Type | Constraints | Description |
|-------|------|------------|-------------|
| subject_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| name | VARCHAR(50) | NOT NULL | Subject name |
| code | VARCHAR(10) | UNIQUE | Subject code |
| class | VARCHAR(20) | NOT NULL | Associated class |
| coefficient | DECIMAL(3,1) | NOT NULL DEFAULT 1.0 | Subject weight |
| category | VARCHAR(50) | | Subject category |

#### Admins
| Field | Type | Constraints | Description |
|-------|------|------------|-------------|
| admin_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| username | VARCHAR(50) | UNIQUE, NOT NULL | Login username |
| password | VARCHAR(255) | NOT NULL | Hashed password |
| email | VARCHAR(100) | UNIQUE | Email address |
| role | ENUM | ('super_admin','sub_admin') | Admin level |
| status | ENUM | ('active','inactive') | Account status |
| last_login | TIMESTAMP | | Last login time |

### Supporting Tables

#### Attendance
| Field | Type | Constraints | Description |
|-------|------|------------|-------------|
| attendance_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| pupil_id | INT | FOREIGN KEY | Reference to pupil |
| date | DATE | NOT NULL | Attendance date |
| status | ENUM | ('present','absent','late') | Status |
| term | TINYINT | NOT NULL | Term number |
| reason | TEXT | | Absence reason |

#### Parent_Accounts
| Field | Type | Constraints | Description |
|-------|------|------------|-------------|
| parent_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| username | VARCHAR(50) | UNIQUE, NOT NULL | Login username |
| password | VARCHAR(255) | NOT NULL | Hashed password |
| email | VARCHAR(100) | UNIQUE | Email address |
| phone | VARCHAR(15) | NOT NULL | Contact number |
| pupils | JSON | NOT NULL | Array of pupil_ids |
| status | ENUM | ('active','inactive') | Account status |

## Project Structure

primary-school-results/
├── app/
│ ├── config/
│ │ ├── database.php
│ │ ├── config.php
│ │ └── constants.php
│ ├── controllers/
│ │ ├── AdminController.php
│ │ ├── TeacherController.php
│ │ ├── PupilController.php
│ │ ├── ResultController.php
│ │ ├── AuthController.php
│ │ └── ParentController.php
│ ├── models/
│ │ ├── Admin.php
│ │ ├── Teacher.php
│ │ ├── Pupil.php
│ │ ├── Result.php
│ │ ├── Subject.php
│ │ ├── Attendance.php
│ │ └── Parent.php
│ ├── views/
│ │ ├── admin/
│ │ │ ├── dashboard.php
│ │ │ ├── teachers/
│ │ │ ├── pupils/
│ │ │ └── results/
│ │ ├── teacher/
│ │ │ ├── dashboard.php
│ │ │ ├── results/
│ │ │ └── attendance/
│ │ ├── parent/
│ │ │ ├── dashboard.php
│ │ │ └── results/
│ │ └── auth/
│ │ ├── login.php
│ │ └── reset-password.php
│ ├── helpers/
│ │ ├── AuthHelper.php
│ │ ├── ValidationHelper.php
│ │ └── PDFHelper.php
│ └── services/
│ ├── ResultCalculationService.php
│ ├── ReportCardService.php
│ └── NotificationService.php
├── public/
│ ├── index.php
│ ├── assets/
│ │ ├── css/
│ │ ├── js/
│ │ ├── images/
│ │ └── fonts/
│ └── uploads/
│ ├── profile-pictures/
│ └── documents/
├── resources/
│ ├── templates/
│ │ ├── emails/
│ │ └── pdf/
│ └── lang/
│ ├── en/
│ └── fr/
├── tests/
│ ├── Unit/
│ └── Integration/
├── vendor/
├── logs/
├── docs/
│ └── CONTEXT.md
├── .env
├── .gitignore
├── composer.json
├── README.md
└── phpunit.xml

### Key Directories Explained

### Key Directories Explained

- **app/**: Core application code
  - **config/**: Configuration files
  - **controllers/**: Request handlers
  - **models/**: Database models
  - **views/**: UI templates
  - **helpers/**: Utility functions
  - **services/**: Business logic

- **public/**: Publicly accessible files
  - **assets/**: Static resources
  - **uploads/**: User-uploaded content

- **resources/**: Application resources
  - **templates/**: Email and PDF templates
  - **lang/**: Language files

- **tests/**: Test files
  - **Unit/**: Unit tests
  - **Integration/**: Integration tests

- **vendor/**: Third-party dependencies
- **logs/**: Application logs
- **docs/**: Documentation files

### Key Files

- **.env**: Environment variables
- **composer.json**: PHP dependencies
- **phpunit.xml**: Testing configuration
- **README.md**: Project documentation

This structure follows the MVC pattern and provides clear separation of concerns while maintaining scalability and maintainability.

## Development Roadmap

### Phase 1: Project Setup & Authentication
1. Initial Setup
   - Configure development environment
   - Set up project structure
   - Initialize Git repository
   - Install dependencies via Composer
   - Configure database connection

2. Authentication System
   - Implement login system for all user types (Admin, Teacher, Parent)
   - Create password reset functionality
   - Set up session management
   - Implement role-based access control

### Phase 2: Core Admin Features
1. User Management
   - Teacher account creation and management
   - Pupil registration system
   - Parent account management
   - User status management (active/inactive)

2. Academic Configuration
   - Subject management
   - Class setup
   - Academic year/term configuration
   - Grade calculation rules setup

### Phase 3: Teacher Features
1. Result Management
   - Mark entry interface for sequences
   - Exam marks recording
   - Validation rules implementation
   - Auto-calculation features

2. Class Management
   - Class roster view
   - Attendance tracking
   - Performance overview
   - Student progress monitoring

### Phase 4: Result Processing
1. Calculations
   - Term average calculation
   - Class ranking system
   - Overall performance metrics
   - Grade aggregation

2. Report Generation
   - Report card template design
   - PDF generation system
   - Bulk report processing
   - Result analysis tools

### Phase 5: Parent Portal
1. Parent Features
   - Result viewing interface
   - Historical performance tracking
   - Communication system
   - Attendance monitoring

### Phase 6: System Enhancement
1. Data Management
   - Backup system
   - Data export/import
   - Archival system
   - Audit logging

2. UI/UX Improvements
   - Responsive design implementation
   - User interface optimization
   - Accessibility improvements
   - Performance optimization

### Phase 7: Testing & Deployment
1. Testing
   - Unit testing
   - Integration testing
   - User acceptance testing
   - Security testing

2. Deployment
   - Server setup
   - Production environment configuration
   - SSL implementation
   - Performance monitoring setup

### Development Priorities
1. **Critical Path**
   - Authentication system
   - Result management
   - Report card generation

2. **Secondary Features**
   - Parent portal
   - Advanced analytics
   - Communication system

3. **Nice-to-Have Features**
   - Mobile app integration
   - SMS notifications
   - Advanced reporting

### Quality Assurance Checklist
- [ ] Security best practices implemented
- [ ] Data validation rules in place
- [ ] Error handling and logging
- [ ] Performance optimization
- [ ] Cross-browser compatibility
- [ ] Mobile responsiveness
- [ ] Backup systems configured
- [ ] User documentation complete

This roadmap provides a structured approach to building the system, focusing on one component at a time while ensuring all critical features are prioritized appropriately.

