import Database from 'better-sqlite3';
import path from 'path';
import fs from 'fs';

const dbPath = path.resolve('lms_v2.db');
export const db = new Database(dbPath);

export function initDb() {
  // Enable foreign keys
  db.exec('PRAGMA foreign_keys = ON');

  // Users Table
  db.exec(`
    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT UNIQUE NOT NULL,
      password TEXT NOT NULL,
      role TEXT CHECK(role IN ('admin', 'dosen', 'mahasiswa')) NOT NULL,
      name TEXT NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
  `);

  // Courses Table (Mata Kuliah)
  db.exec(`
    CREATE TABLE IF NOT EXISTS courses (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      code TEXT UNIQUE NOT NULL,
      description TEXT,
      lecturer_id INTEGER NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (lecturer_id) REFERENCES users(id)
    )
  `);

  // Classes Table (Kelas) - Linked to Course
  db.exec(`
    CREATE TABLE IF NOT EXISTS classes (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      course_id INTEGER NOT NULL,
      name TEXT NOT NULL,
      code TEXT UNIQUE NOT NULL,
      lecturer_id INTEGER NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
      FOREIGN KEY (lecturer_id) REFERENCES users(id)
    )
  `);

  // Class Members (Students in Classes)
  db.exec(`
    CREATE TABLE IF NOT EXISTS class_members (
      class_id INTEGER NOT NULL,
      student_id INTEGER NOT NULL,
      PRIMARY KEY (class_id, student_id),
      FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
      FOREIGN KEY (student_id) REFERENCES users(id)
    )
  `);

  // Materials
  db.exec(`
    CREATE TABLE IF NOT EXISTS materials (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      class_id INTEGER NOT NULL,
      meeting_id INTEGER,
      title TEXT NOT NULL,
      content TEXT,
      type TEXT DEFAULT 'text',
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
      FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE
    )
  `);

  // Assignments
  db.exec(`
    CREATE TABLE IF NOT EXISTS assignments (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      class_id INTEGER NOT NULL,
      meeting_id INTEGER,
      title TEXT NOT NULL,
      description TEXT,
      due_date DATETIME,
      type TEXT DEFAULT 'tugas', -- 'latihan', 'tugas', 'ujian'
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
      FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE
    )
  `);

  // Main Questions (Parent of Questions)
  db.exec(`
    CREATE TABLE IF NOT EXISTS main_questions (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      assignment_id INTEGER NOT NULL,
      title TEXT,
      description TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE
    )
  `);

  // Questions (Soal)
  db.exec(`
    CREATE TABLE IF NOT EXISTS questions (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      assignment_id INTEGER NOT NULL,
      main_question_id INTEGER, -- Link to Main Question
      question_text TEXT NOT NULL,
      question_type TEXT DEFAULT 'essay', -- 'essay', 'multiple_choice'
      options TEXT, -- JSON array for multiple choice options
      correct_answer TEXT,
      points INTEGER DEFAULT 0,
      FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
      FOREIGN KEY (main_question_id) REFERENCES main_questions(id) ON DELETE CASCADE
    )
  `);

  // Submissions
  db.exec(`
    CREATE TABLE IF NOT EXISTS submissions (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      assignment_id INTEGER NOT NULL,
      student_id INTEGER NOT NULL,
      content TEXT,
      grade INTEGER,
      feedback TEXT,
      submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
      FOREIGN KEY (student_id) REFERENCES users(id)
    )
  `);

  // Student Answers
  db.exec(`
    CREATE TABLE IF NOT EXISTS student_answers (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      submission_id INTEGER NOT NULL,
      question_id INTEGER NOT NULL,
      answer_text TEXT,
      is_correct BOOLEAN,
      FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
      FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
    )
  `);

  // Messages (Chat)
  db.exec(`
    CREATE TABLE IF NOT EXISTS messages (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      sender_id INTEGER NOT NULL,
      receiver_id INTEGER, -- Null for group chat
      class_id INTEGER, -- For group chat context
      content TEXT NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (sender_id) REFERENCES users(id),
      FOREIGN KEY (receiver_id) REFERENCES users(id),
      FOREIGN KEY (class_id) REFERENCES classes(id)
    )
  `);

  // Grading Settings
  db.exec(`
    CREATE TABLE IF NOT EXISTS grading_settings (
      class_id INTEGER NOT NULL,
      type TEXT NOT NULL, -- 'latihan', 'tugas', 'ujian'
      weight INTEGER NOT NULL DEFAULT 0,
      PRIMARY KEY (class_id, type),
      FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    )
  `);

  // Announcements Table
  db.exec(`
    CREATE TABLE IF NOT EXISTS announcements (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      title TEXT NOT NULL,
      content TEXT NOT NULL,
      author_id INTEGER NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (author_id) REFERENCES users(id)
    )
  `);

  // Meetings Table (Pertemuan)
  db.exec(`
    CREATE TABLE IF NOT EXISTS meetings (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      class_id INTEGER NOT NULL,
      title TEXT NOT NULL,
      description TEXT,
      date DATETIME DEFAULT CURRENT_TIMESTAMP,
      learning_model TEXT DEFAULT 'none', -- 'none', 'pbl', 'pjbl'
      learning_syntax TEXT, -- JSON string storing steps
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    )
  `);

  // Groups
  db.exec(`
    CREATE TABLE IF NOT EXISTS groups (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      class_id INTEGER NOT NULL,
      name TEXT NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    )
  `);

  // Group Members
  db.exec(`
    CREATE TABLE IF NOT EXISTS group_members (
      group_id INTEGER NOT NULL,
      student_id INTEGER NOT NULL,
      PRIMARY KEY (group_id, student_id),
      FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
      FOREIGN KEY (student_id) REFERENCES users(id)
    )
  `);

  // Attendance Table
  db.exec(`
    CREATE TABLE IF NOT EXISTS attendance (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      class_id INTEGER NOT NULL,
      meeting_id INTEGER NOT NULL,
      code TEXT NOT NULL,
      status TEXT DEFAULT 'open', -- 'open', 'closed'
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
      FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE
    )
  `);

  // Attendance Submissions (Students checking in)
  db.exec(`
    CREATE TABLE IF NOT EXISTS attendance_submissions (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      attendance_id INTEGER NOT NULL,
      student_id INTEGER NOT NULL,
      submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (attendance_id) REFERENCES attendance(id) ON DELETE CASCADE,
      FOREIGN KEY (student_id) REFERENCES users(id),
      UNIQUE(attendance_id, student_id)
    )
  `);

  // External References
  db.exec(`
    CREATE TABLE IF NOT EXISTS external_references (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      title TEXT NOT NULL,
      author TEXT,
      field TEXT,
      publisher TEXT,
      file_url TEXT,
      content TEXT,
      lecturer_id INTEGER NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (lecturer_id) REFERENCES users(id)
    )
  `);

  // RAG Documents
  db.exec(`
    CREATE TABLE IF NOT EXISTS rag_documents (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      title TEXT NOT NULL,
      content TEXT NOT NULL,
      lecturer_id INTEGER NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (lecturer_id) REFERENCES users(id)
    )
  `);

  // --- MIGRATIONS (Safe to run multiple times) ---
  try { db.exec("ALTER TABLE meetings ADD COLUMN learning_model TEXT DEFAULT 'none'"); } catch (e) {}
  try { db.exec("ALTER TABLE meetings ADD COLUMN learning_syntax TEXT"); } catch (e) {}
  try { db.exec("ALTER TABLE materials ADD COLUMN meeting_id INTEGER REFERENCES meetings(id) ON DELETE CASCADE"); } catch (e) {}
  try { db.exec("ALTER TABLE assignments ADD COLUMN meeting_id INTEGER REFERENCES meetings(id) ON DELETE CASCADE"); } catch (e) {}
  try { db.exec("ALTER TABLE assignments ADD COLUMN type TEXT DEFAULT 'tugas'"); } catch (e) {}
  try { db.exec("ALTER TABLE messages ADD COLUMN is_read BOOLEAN DEFAULT 0"); } catch (e) {}
  try { db.exec("ALTER TABLE assignments ADD COLUMN work_type TEXT DEFAULT 'individu'"); } catch (e) {}
  try { db.exec("ALTER TABLE messages ADD COLUMN group_id INTEGER REFERENCES groups(id) ON DELETE CASCADE"); } catch (e) {}
  try { db.exec("ALTER TABLE questions ADD COLUMN main_question_id INTEGER REFERENCES main_questions(id) ON DELETE CASCADE"); } catch (e) {}
}
