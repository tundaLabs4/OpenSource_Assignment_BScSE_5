# Software Project Tracking System

**Degree Program:** Bachelor of Science in Software Engineering (BScSE)

**Group Number:** 05

---

## Project Overview

The Software Project Tracking System is a web-based application that enables development teams to efficiently manage and monitor their software projects. Users can record new project information, track the current status of each project through a visual dashboard, and search for projects by name. The system features user authentication, role-based access (admin and non-admin), client-side form validation, project sorting, and a clean responsive interface.

This project was developed as part of the **CP 222 - Open Source Technologies** course, demonstrating collaborative software development using Git and GitHub.

---

## Technologies Used

| Technology   | Purpose                           |
|--------------|-----------------------------------|
| PHP 8.x      | Server-side scripting language    |
| MySQL / MariaDB | Relational database            |
| HTML5 / CSS3 | Frontend structure and styling    |
| JavaScript   | Client-side validation            |
| Git          | Version control                   |
| GitHub       | Remote repository hosting         |

---

## Installation Steps

### Prerequisites

Ensure the following software is installed on your system:

- **PHP** (version 8.0 or higher) with `mysqli` extension
- **MySQL** (version 5.7 or higher) or **MariaDB** (version 10.3 or higher)
- **Apache** / **Nginx** / or PHP built-in server
- **Git** (for cloning the repository)
- A web browser

### Step 1: Clone the Repository

```bash
git clone https://github.com/tundaLabs4/OpenSource_Assignment_BScSE_05.git
cd OpenSource_Assignment_BScSE_05
```

### Step 2: Configure the Database

1. Open your MySQL client.
2. Run the `create.php` setup script by navigating to `http://localhost:8000/create.php` in your browser, OR run the SQL file directly:

```bash
mysql -u root -p < phpproject.sql
```

This creates the `open_source_project` database with the following tables:
- `users` — Stores user credentials (bcrypt hashed passwords) with admin flags
- `category` — Lookup table for project categories
- `status` — Lookup table for project statuses
- `projects` — Stores all project records with category, status, sort order, and privacy settings

### Step 3: Configure Database Connection

Edit `connect.php` in the project root and update the credentials:

```php
$conn = new mysqli("localhost", "root", "YOUR_PASSWORD", "open_source_project");
```

### Step 4: Start the Web Server

If using PHP's built-in server:

```bash
php -S localhost:8000
```

Or deploy the project folder to your Apache web server's document root.

### Step 5: Access the Application

Open your web browser and navigate to:

```
http://localhost:8000          (PHP built-in server)
http://localhost/OpenSource_Assignment_BScSE_05  (Apache)
```

### Default Login

| Username | Password | Role  |
|----------|----------|-------|
| User123  | a        | Admin |

**Important:** Delete or rename `create.php` after initial setup for security.

---

## Features

- **User Authentication** — Register, login, logout with session management
- **Role-Based Access** — Admin and non-admin user roles; only admins can promote users
- **Project Management** — Create, edit, delete, and reorder projects
- **Category Management** — Organize projects by category with delete protection
- **Status Management** — Track project progress with custom statuses
- **Project Sorting** — Sort by any column (name, date, category, status, last changed)
- **Private Projects** — Mark projects as private (admin-only editing)
- **Search Projects** — Find projects by name via sortable table headers
- **Client-Side Validation** — Real-time form validation with inline error messages
- **Flash Messages** — Success/error notifications after form submissions

---

## Git Commands Used

Below are the key Git commands executed throughout the project lifecycle:

```bash
# Initialize the repository
git init

# Add remote origin
git remote add origin https://github.com/tundaLabs4/OpenSource_Assignment_BScSE_05.git

# Create initial commit
git add README.md connect.php create.php functions.php style.css js/validation.js
git commit -m "Initial commit: Set up repository structure, database configuration, and styling"

# Add authentication module
git add login.php logout.php register.php loggedin.php
git commit -m "Implement user authentication module - login, logout, and registration"

# Add core CRUD functionality
git add projects.php editprojects.php cat.php editcat.php status.php editstatus.php
git commit -m "Add core CRUD operations for projects, categories, and statuses"

# Add user management
git add users.php editusers.php
git commit -m "Add user management with admin and non-admin role support"

# Add client-side validation and UI enhancements
git add js/validation.js
git commit -m "Implement client-side form validation with real-time feedback"

# Add dashboard and navigation
git add index.php tables.php
git commit -m "Add dashboard page and navigation bar"

# Update README documentation
git add README.md
git commit -m "Update README.md with project documentation, setup instructions, and git commands"

# Create and switch to development branch
git checkout -b development

# Add a feature on development branch (enhanced search / sort indicators)
git add projects.php style.css
git commit -m "Add sort direction indicators and improved table interactions"

# Switch back to main and merge development
git checkout main
git merge development --no-ff -m "Merge development branch: add sort indicators and table enhancements"

# Push to remote repository
git push -u origin main
git push origin development
```

---

## Team Members (Group 05)

| Member | Role | Responsibilities |
|--------|------|------------------|
| NOUMANTUNDA | Git Lead & Database Administrator | Repository setup, README, database schema, connect.php |
| DAN KALABWE | Authentication Core | login.php, logout.php, loggedin.php |
| EMMANUEL STANSLAUS | User Registration & Layout | register.php, tables.php, CSS styling |
| BORN BRIAN | Project Ingestion Backend | editprojects.php (create/update/delete) |
| FRANK GORDIUS | Project Creation Frontend UI | projects.php (list, sort, display) |
| CHANZI ANDERSON | Dashboard & Navigation | index.php, navigation integration |
| OMARI EZEKIEL | Category & Status Management | cat.php, editcat.php, status.php, editstatus.php |
| MONALISA YUDAH | User Management | users.php, editusers.php |
| GODFREY MASINE | Client-Side Validation | js/validation.js, form validation integration |
| HELENA THEOPHIL | Documentation & Reporting | README.md, final report |

---

## GitHub Repository

**Repository URL:** [https://github.com/tundaLabs4/OpenSource_Assignment_BScSE_05](https://github.com/tundaLabs4/OpenSource_Assignment_BScSE_05)

---

## License

This project is developed for educational purposes as part of the CP 222 - Open Source Technologies course assignment.
