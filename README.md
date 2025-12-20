# Music Score Inventory & Loan System 🎼

A specialized web application developed to manage the physical sheet music library of a musical institution. This system was designed to solve the common problem of lost materials by implementing a rigorous digital control over physical loans and inventory status.

## 🎯 Project Overview
In large orchestras or music schools, losing physical scores is a significant financial and academic loss. This system automates the "check-out" and "check-in" process, providing real-time visibility into who possesses specific materials and their expected return dates.

## 🛠️ Technical Stack
- **Framework:** Laravel (PHP).
- **Frontend:** Blade Templating Engine + Bootstrap (or Tailwind) for responsive design.
- **Database:** MySQL/PostgreSQL (Relational schema for inventory tracking).
- **Tools:** Composer, Artisan CLI.

## 🚀 Core Functionalities
- **Sheet Music Catalog:** Detailed database of scores, including composer, genre, arrangement type, and physical location.
- **Loan Management System:** Track active loans, historical data of users, and pending returns.
- **User Roles:** Administrative access for librarians and view-only/request access for musicians.
- **Status Tracking:** Real-time availability updates (Available, Loaned, Damaged, Lost).
- **Automated Alerts:** (Optional/Planned) System to flag overdue materials.

## 📁 Key Technical Implementations
- **Eloquent ORM:** Leveraged for complex relationships between `Scores`, `Users`, and `Loans`.
- **Migrations & Seeders:** Structured database versioning for consistent development environments.
- **Validation Logic:** Backend rules to prevent the loan of already-out materials.

## ⚙️ Setup and Installation
1. Clone the repository: 
   `git clone https://github.com/maferCachopo/servicio_comunitario.git`
2. Install dependencies:
   `composer install`
3. Configure your environment:
   - Copy `.env.example` to `.env`.
   - Setup your database credentials.
4. Generate App Key & Migrate:
   `php artisan key:generate`
   `php artisan migrate`
5. Run the server:
   `php artisan serve`

## 🧠 Engineering Insights
This project demonstrates my ability to:
- Translate a real-world organizational problem into a **Relational Database Model**.
- Utilize **Laravel's MVC architecture** to build a scalable and maintainable tool.
- Bridge the gap between **Musical Administration** and **Software Engineering**.

---
**Maria Fernanda Cachopo Rojas** *Computer Engineering Student | Full Stack Developer* 📫 [mafecaro2000@gmail.com](mailto:mafecaro2000@gmail.com)
