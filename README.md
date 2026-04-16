
# Pro2LMS — Advanced Learning Management System

**Pro2LMS** is a feature-rich, highly flexible Learning Management System (LMS) designed to stand out from conventional platforms through its comprehensive features, responsiveness, and user-friendly experience across all devices.

---

## Overview

Pro2LMS supports three levels of user access:

- **Admin** — platform management and oversight
- **Lecturer** — course creation, material delivery, and assessment management
- **Student** — interactive learning, collaboration, and examination

---

## Key Features

### 🧩 Customizable Learning Syntax
Lecturers can design interactive, structured learning plans and curricula. Beyond listing learning stages, this feature allows embedding additional sub-features within each learning step, enabling a truly dynamic course structure.

### 🔔 Live Notifications
Built on **Firebase**, the live notification system keeps all user levels informed in real time — covering learning activities, group discussions, assignment deadlines, exam schedules, and FAQ updates.

### 🤖 AI Assistant
An AI-powered assistant integrated with the **Anthropic Claude LLM API**, helping students accelerate their learning by instantly answering questions and explaining course materials interactively.

### 📄 RAG Chatbot
A Retrieval-Augmented Generation (RAG) chatbot that provides accurate, document-specific answers. Lecturers upload PDF materials, and the chatbot allows students to query those documents directly. Built with **Python** and **LangChain**, it converts PDF content into vector embeddings stored in **MongoDB**.

### ✍️ Custom Text Editor
A rich text editor built on **TinyMCE** and **MathLive.js**, giving both lecturers and students a flexible space to write content, answer assignments, and solve exams. MathLive integration supports complex mathematical operations — including calculus — without requiring users to manually write LaTeX.

### 💬 Realtime Chat
A multi-level real-time chat system powered by **Firebase**, enabling seamless communication between students and lecturers. Supports:
- Class-wide announcements
- Class discussions
- Small group discussions

### 📝 Exam Feature
Pro2LMS supports a structured exam system with three access modes:

| Mode | Description |
|------|-------------|
| **Open Mode** | Students may access any material, including the AI Assistant. |
| **Materials Only Mode** | Students are restricted to lecturer-provided materials only. Full-screen is enforced; exiting more than 3 times triggers automatic submission. |
| **Closed Mode** | No external material access allowed. Students must remain in full-screen mode for the entire exam duration. |

### 📲 PWA (Progressive Web App)
Pro2LMS is installable as a PWA on any device, giving lecturers and students a native app-like experience directly from their browsers.

---

## Tech Stack

| Layer | Technologies |
|-------|-------------|
| Frontend | React, Tailwind CSS, TinyMCE, MathLive.js |
| Backend | (Full Stack — details per module) |
| AI / Chatbot | Anthropic Claude API, Python, LangChain |
| Real-time | Firebase (Notifications & Chat) |
| Database | MySQL (main application), MongoDB (vector store for RAG Chatbot only) |
| Deployment | PWA-ready |

---

## Author

**Abdussalam Aswin Hadist**  
Full Stack Web Developer  
[github.com/Abdussalam50](https://github.com/Abdussalam50) · [linkedin.com/in/abdussalam-hadist-02067b287](https://linkedin.com/in/abdussalam-hadist-02067b287)

