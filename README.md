# Community Service Management System - UNET 🏫

A robust desktop application developed as part of my **Computer Engineering** degree at the Universidad Nacional Experimental del Táchira (UNET). This system automates the administration, tracking, and validation of community service projects for university students.

## 🎯 Project Overview
The software provides a centralized platform for managing the lifecycle of community service, from initial student registration to the final validation of completed hours. It replaces manual spreadsheet tracking with a structured, database-driven solution.

## 🛠️ Technical Stack
- **Language:** C# (.NET Framework).
- **Architecture:** Model-View-Controller (MVC) for clear separation of concerns.
- **Data Persistence:** Entity Framework with SQL Server for relational data management.
- **UI/UX:** Windows Forms (WinForms) with custom styling for an intuitive administrative dashboard.

## 🚀 Core Functionalities
- **Student & Tutor Management:** Full CRUD (Create, Read, Update, Delete) operations for managing participant profiles.
- **Project Tracking:** Real-time monitoring of project status, assigned hours, and institutional partners.
- **Automated Reporting:** Generates performance summaries and compliance documents for academic departments.
- **Data Validation:** Built-in logic to ensure data integrity and prevent scheduling conflicts.

## 📁 Project Structure
Based on the repository's architecture:
- `Models/`: Data structures and database context definitions.
- `Views/`: User interface components and form designs.
- `Controllers/`: Business logic and communication between UI and Data layers.

## ⚙️ Setup and Installation
1. Clone the repository: 
   `git clone https://github.com/maferCachopo/servicio_comunitario.git`
2. Open the `.sln` file in **Visual Studio 2022** (or later).
3. Restore NuGet packages.
4. Update the connection string in `App.config` to point to your local SQL Server instance.
5. Build and run (F5).

## 🧠 Engineering Insights
This project served as a deep dive into:
- **Relational Database Design:** Normalizing data to handle complex relationships between students, projects, and hours.
- **Design Patterns:** Implementing MVC to ensure the code is maintainable and scalable.
- **Asynchronous Programming:** Managing data operations without freezing the UI.

---
**Maria Fernanda Cachopo Rojas** *Computer Engineering Student | Full Stack Developer* 📫 [mafecaro2000@gmail.com](mailto:mafecaro2000@gmail.com) | [LinkedIn Profile]
