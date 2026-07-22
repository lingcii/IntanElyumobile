# 🌴 Intan-Elyu Tourism Management System

Welcome to the **Intan-Elyu Tourism Management System**! This platform is designed to digitize and enhance the tourism experience in La Union (Elyu) by providing a comprehensive suite of tools for tourists, local government units (LGUs), and administrators.

---

## 🏗️ System Architecture

The project is structured into three main components:

### 1. 📱 Mobile App (Frontend)
Located in `Frontend/Mobile/src/`
- **Purpose**: A mobile-first Progressive Web App (PWA) designed for tourists.
- **Key Features**:
  - 🗺️ **Interactive Maps**: Powered by MapLibre GL JS, featuring 3D terrain models and province-masking for a beautiful, immersive experience.
  - 📅 **Itinerary Planner**: Users can create, save, and manage their trips, complete with budget tracking.
  - 📍 **GPS Check-ins**: Gamified location tracking allows tourists to "check-in" at destinations and earn XP.
  - 🛍️ **Merch Store**: Browse and redeem tourism merchandise.
  - 🏆 **Leaderboards**: Compete with other tourists based on XP earned from visiting spots.

### 2. 💻 Web Dashboard (Frontend)
Located in `Frontend/Website/Frontend/`
- **Purpose**: An administrative dashboard for the Local Government Unit (LGU) and Provincial Tourism Office (LUPTO).
- **Key Features**:
  - 📊 **Analytics Dashboard**: View insights, visitor trends, and system statistics.
  - 📍 **Destination Management**: Add, approve, or edit tourist spots.
  - 🚌 **Fare Data Management**: Upload and manage transportation fare matrices.
  - 👕 **Merch Management**: Handle inventory and process reservations.
  - 🖨️ **Report Generation**: Export tourism data and analytics.

### 3. ⚙️ Backend API
Located in `backend/`
- **Purpose**: The core engine powering both frontends, built with **Laravel**.
- **Key Features**:
  - 🔐 **Authentication**: Secure login system with role-based access control (Tourist, LGU, LUPTO).
  - 🗄️ **Database**: Uses MySQL (hosted remotely) with a well-structured schema for users, itineraries, tourist spots, and logs.
  - 📡 **REST API**: Provides robust endpoints for map data, check-ins, leaderboards, and administrative tasks.

---

## 🛠️ Technology Stack

- **Frontend (Mobile)**: HTML5, CSS3 (Custom Glassmorphism & Animations), JavaScript, PHP
- **Frontend (Web)**: HTML5, CSS3, JavaScript, PHP
- **Map Engine**: MapLibre GL JS, OpenStreetMap Tiles, CartoDB Basemaps
- **Backend Framework**: Laravel (PHP)
- **Database**: MySQL

---

## 🚀 Setup & Installation

### Prerequisites
- PHP >= 8.1
- Composer
- MySQL Database

### Backend Setup
1. Navigate to the `backend/` directory.
2. Install dependencies:
   ```bash
   composer install
   ```
3. Copy the environment file and generate an app key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. Configure your `.env` with your database credentials.
5. Run migrations to set up the database structure:
   ```bash
   php artisan migrate
   ```
6. Start the Laravel development server:
   ```bash
   php artisan serve
   ```

### Frontend Setup
1. Serve the `Frontend/Mobile/src/` or `Frontend/Website/Frontend/` directories using any local PHP server (e.g., XAMPP, Laragon, or PHP's built-in server).
2. Ensure the frontends point to the correct backend API URL (usually configured in the respective Javascript/PHP files).

---

## ✨ Design Philosophy

The Intan-Elyu system prioritizes **Visual Excellence**. 
The mobile app features modern glassmorphism, dynamic micro-animations, and curated color palettes designed to provide tourists with a highly premium, engaging, and butter-smooth user experience.
