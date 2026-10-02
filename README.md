# Tamkeen – AI-Powered Skill-Based Employment Platform

Tamkeen is a skill-based employment platform designed to help organizations evaluate candidates through practical challenges rather than relying only on CVs. The platform combines challenge-based assessments, AI-assisted evaluation, browser proctoring, HR workflows, and training opportunities.

## Project Overview

Traditional recruitment often relies heavily on CVs, which may not fully demonstrate a candidate's practical abilities. Tamkeen addresses this by allowing job seekers to complete practical challenges and giving employers tools to review and evaluate those submissions.

The platform also provides training and learning opportunities for students and fresh graduates.

## Key Features

- Challenge-based skill assessment
- AI-assisted candidate evaluation using Gemini
- Browser proctoring and assessment integrity monitoring
- HR candidate review and shortlisting workflows
- Company and HR management
- Job seeker profiles and QR-based public profiles
- Training opportunities and applications
- Learning/community features
- Task management and evaluation

## User Roles

- Job Seeker
- HR Manager
- Company Admin
- Task Manager
- Training Manager

## Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend:** PHP
- **Database:** MySQL / MariaDB
- **AI:** Google Gemini API
- **Client-side monitoring:** MediaPipe / browser-based proctoring components

## Project Structure

```text
css/                 Stylesheets
js/                  JavaScript files
img/                 Project images/assets
includes/            Shared PHP components
public/              Public-facing pages and functions
private-user/        Job seeker area
private-hr/          HR area
private-task/        Task and AI evaluation area
private-training/    Training management area
lib/                 Frontend libraries
data/                Static JSON data
database/schema.sql  Database structure without demo/user data
docs/                Project presentation
```

## Local Setup

1. Install a PHP + MySQL/MariaDB environment such as XAMPP.
2. Create a database named `tamkeen`.
3. Import `database/schema.sql` into the database.
4. Copy `config.example.php` to `config.php` and update the local database settings.
5. Copy `private-config/gemini-key.example.php` to `private-config/gemini-key.php`.
6. Add your own Gemini API key to the local `gemini-key.php` file.
7. Place the project inside your local web server directory and open it through Apache.

## Security Note

API keys, local configuration files, uploaded CVs, task submissions, and other user-generated files are intentionally excluded from this public repository. Do not commit real credentials, API keys, passwords, or personal documents.

## Project Presentation

The `docs/` folder contains the project presentation with the problem, solution, user roles, key features, workflow, and technology stack.

## Academic Project

Tamkeen was developed as a university graduation project by a three-person team during the 2025–2026 academic year.
