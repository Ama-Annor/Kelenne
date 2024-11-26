# Kelenne Car Wash Management System

A comprehensive web-based platform for managing Kelenne Car Wash services, appointments, and customer relationships.

## Overview

Kelenne Car Wash is a professional car washing service system that provides various vehicle care services. This web application streamlines the management of car wash operations, from booking appointments to managing services and tracking customer relationships.

## Features

- **User Authentication**
  - Customer registration and login
  - Secure admin dashboard access
  - Profile management

- **Service Management**
  - Multiple service types (Express Wash, Premium Detailing, Paint Protection)
  - Service scheduling and availability tracking
  - Pricing management

- **Appointment System**
  - Real-time booking system
  - Calendar integration
  - Appointment status tracking
  - Email notifications

- **Admin Dashboard**
  - Appointment management
  - Employee scheduling
  - Inventory tracking
  - Revenue analytics
  - Customer database
  - Equipment management
  - Promotions and rewards system

- **Customer Features**
  - Service booking
  - Appointment history
  - Service status tracking
  - Profile management

## Tech Stack

- **Frontend:**
  - HTML5
  - CSS3
  - JavaScript
  - Boxicons for icons
  - Flatpickr for date picking

- **Backend:**
  - PHP
  - MySQL Database

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/kelenne-carwash.git
```

2. Set up the database:
   - Create a MySQL database
   - Import the provided SQL schema
   - Update database credentials in `db/database.php`

3. Configure your web server:
   - Point the document root to the project directory
   - Ensure PHP is properly configured

4. Install dependencies:
   - Ensure all CSS and JavaScript dependencies are properly linked
   - Update API keys and configuration settings as needed

## Project Structure

```
kelenne-carwash/
├── assets/
│   ├── css/
│   ├── images/
│   └── js/
├── db/
│   └── database.php
├── view/
│   ├── admin/
│   ├── services.html
│   ├── about.php
│   ├── contact.php
│   └── ...
└── index.html
```

## Usage

1. **Customer Interface:**
   - Visit the homepage
   - Browse available services
   - Register/Login to book appointments
   - Manage appointments and profile

2. **Admin Dashboard:**
   - Access via admin login
   - Manage appointments and services
   - Track inventory and revenue
   - Monitor customer data

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---