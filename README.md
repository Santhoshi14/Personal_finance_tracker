# Personal Finance Tracker

A simple and user-friendly web application built using **PHP**, **MySQL**, **HTML**, **CSS**, and **JavaScript** to help users track their daily financial activities.  
This project allows users to record their transactions, add incomes, manage wallets, and view their monthly spending or earnings in a clean dashboard.

---

## 🚀 Features

### 🔐 User Authentication
- User signup and login system  
- Passwords are securely hashed using `password_hash()`  
- Unique tables are created automatically for every user  
  - `transactions_<userid>`
  - `income_<userid>`

### 💰 Income Management
- Add income with source, date, and amount  
- Shows month-wise total income  
- Displays total income at the bottom  
- Data updated using AJAX (no page refresh)

### 💳 Transaction Management
- Add expenses or other transactions  
- Categorize transactions (food, travel, bills, etc.)  
- Each user's transactions stored in their own table  
- View and analyze monthly transactions

### 👛 Wallet Management
- Create and manage wallets  
- Update wallet balance  
- Connect transactions to specific wallets

### 📊 Visualization
- Dashboard shows:
  - Monthly income  
  - Monthly expenses  
  - Observations & summary  

---

## 🛠️ Technologies Used

| Technology | Purpose |
|-----------|---------|
| **PHP** | Backend development |
| **MySQL** | Database |
| **HTML/CSS** | Web UI |
| **JavaScript** | Dynamic frontend behavior |
| **AJAX** | Fetch & update data without page reload |
| **XAMPP** | Local server setup |

---

## 📂 Project Structure

