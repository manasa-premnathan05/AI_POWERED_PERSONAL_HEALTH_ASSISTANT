## AI Powered Personal Health Assistant (CareSphere)

**CareSphere** is an AI-powered web application designed to help users manage their health and wellness through a comprehensive set of digital tools. Built primarily with PHP and modern web technologies, this personal health assistant enables users to track symptoms, manage appointments and medications, set health goals, receive reminders, and interact with an AI chatbot for health advice.

### Key Features

- **AI Symptom Checker**: Users can enter their symptoms, age, and gender, and receive AI-generated health insights and recommendations.
- **Personal Dashboard**: Centralized dashboard for tracking health metrics, goals, appointments, and medication schedules.
- **Reminders System**: Automated reminders for medications, appointments, and custom health tasks.
- **Nearby Healthcare Facilities**: Integrated map to locate nearby hospitals and clinics based on the user’s current location.
- **User Reviews & Testimonials**: Collects user feedback and displays testimonials to help build a community of health-conscious users.
- **Newsletter Subscription**: Users can subscribe to receive the latest health news and updates.
- **AI Chatbot Integration**: Embedded chatbot for personalized health guidance and quick answers to user queries.
- **Responsive Design**: Mobile-friendly interface with modern, accessible UI components.

### Technical Stack

- **Backend**: PHP 8.x (with Apache), Composer for dependency management
  - Uses libraries such as `phpmailer/phpmailer` for sending emails and `peppeocchi/php-cron-scheduler` for automated tasks (cron jobs).
- **Frontend**: HTML5, CSS3, JavaScript (with integration of third-party APIs like NewsAPI and OpenStreetMap/Leaflet for news and maps)
- **Containerization**: Dockerfile provided for easy deployment in a containerized environment
- **Database**: MySQL/MariaDB (configuration and connection handled in PHP includes)

### Main Modules

- `index.php`: Entry point and landing page for the application.
- `dashboard.php`: User dashboard summarizing all health data and actions.
- `symptom-checker.php`: Interactive AI-powered symptom analysis form.
- `reminders.php`, `add_appointment.php`, `add_medication.php`: Tools for scheduling and managing health events.
- `about.php`, `profile_setup.php`, `edit_profile.php`: User profile and information management.
- `assets/`, `style.css`: Frontend assets and styling for the UI.
- `Dockerfile`: Container deployment configuration.

### Getting Started

1. **Clone the repository**
2. **Configure your environment**: Set up `.env` and database credentials as needed.
3. **Install dependencies**: Use Composer as described in `composer.json`.
4. **Deploy with Docker**: Use the provided `Dockerfile` for a quick setup.
5. **Access the application via your web browser**.

---

You can view all files and explore the source code here: [GitHub Repository Contents](https://github.com/amalvarghese-30/AI_POWERED_PERSONAL_HEALTH_ASSISTANT/tree/main/)
