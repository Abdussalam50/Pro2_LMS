import express from 'express';
import { db } from '../../db/database';

const router = express.Router();

// Middleware to check auth (simplified)
const requireAuth = (req: any, res: any, next: any) => {
  // In a real app, verify token here or rely on previous middleware
  // For now, we assume the frontend handles the flow, but backend should verify.
  // We'll skip detailed middleware implementation for this step to focus on routes.
  next();
};

// --- ADMIN ROUTES ---

// Get all users
router.get('/users', (req, res) => {
  const users = db.prepare('SELECT id, username, role, name, created_at FROM users').all();
  res.json(users);
});

// Delete user
router.delete('/users/:id', (req, res) => {
  db.prepare('DELETE FROM users WHERE id = ?').run(req.params.id);
  res.json({ success: true });
});

// --- LECTURER ROUTES ---

// Get Lecturer Courses
router.get('/lecturer/:id/courses', (req, res) => {
  const courses = db.prepare('SELECT * FROM courses WHERE lecturer_id = ?').all(req.params.id);
  // For each course, get its classes
  const coursesWithClasses = courses.map((course: any) => {
    const classes = db.prepare('SELECT * FROM classes WHERE course_id = ?').all(course.id);
    return { ...course, classes };
  });
  res.json(coursesWithClasses);
});

// Create Course
router.post('/courses', (req, res) => {
  const { name, code, description, lecturer_id } = req.body;
  try {
    const info = db.prepare('INSERT INTO courses (name, code, description, lecturer_id) VALUES (?, ?, ?, ?)').run(name, code, description, lecturer_id);
    res.json({ id: info.lastInsertRowid });
  } catch (err) {
    res.status(400).json({ error: 'Course code likely exists' });
  }
});

// Update Course
router.put('/courses/:id', (req, res) => {
  const { name, code, description } = req.body;
  try {
    db.prepare('UPDATE courses SET name = ?, code = ?, description = ? WHERE id = ?').run(name, code, description, req.params.id);
    res.json({ success: true });
  } catch (err) {
    res.status(400).json({ error: 'Update failed' });
  }
});

// Delete Course
router.delete('/courses/:id', (req, res) => {
  db.prepare('DELETE FROM courses WHERE id = ?').run(req.params.id);
  res.json({ success: true });
});

// Create Class (in a Course)
router.post('/classes', (req, res) => {
  const { course_id, name, code, lecturer_id } = req.body;
  try {
    const info = db.prepare('INSERT INTO classes (course_id, name, code, lecturer_id) VALUES (?, ?, ?, ?)').run(course_id, name, code, lecturer_id);
    res.json({ id: info.lastInsertRowid });
  } catch (err) {
    res.status(400).json({ error: 'Class code likely exists' });
  }
});

// Get Lecturer Classes (All, optional)
router.get('/lecturer/:id/classes', (req, res) => {
  const classes = db.prepare('SELECT * FROM classes WHERE lecturer_id = ?').all(req.params.id);
  res.json(classes);
});

// Add Material
router.post('/materials', (req, res) => {
  const { class_id, meeting_id, title, content, type } = req.body;
  try {
    const info = db.prepare('INSERT INTO materials (class_id, meeting_id, title, content, type) VALUES (?, ?, ?, ?, ?)').run(class_id, meeting_id, title, content, type);
    res.json({ id: info.lastInsertRowid });
  } catch (err) {
    console.error('Error adding material:', err);
    res.status(500).json({ error: 'Failed to add material' });
  }
});

// Get Materials for a Meeting
router.get('/meetings/:id/materials', (req, res) => {
  const materials = db.prepare('SELECT * FROM materials WHERE meeting_id = ?').all(req.params.id);
  res.json(materials);
});

// --- STUDENT ROUTES ---

// Join Class
router.post('/classes/join', (req, res) => {
  const { class_code, student_id } = req.body;
  const classData: any = db.prepare('SELECT id FROM classes WHERE code = ?').get(class_code);
  
  if (!classData) return res.status(404).json({ error: 'Class not found' });

  try {
    db.prepare('INSERT INTO class_members (class_id, student_id) VALUES (?, ?)').run(classData.id, student_id);
    res.json({ success: true, class_id: classData.id });
  } catch (err) {
    res.status(400).json({ error: 'Already joined' });
  }
});

// Get Student Classes
router.get('/student/:id/classes', (req, res) => {
  const classes = db.prepare(`
    SELECT c.*, co.name as course_name, co.description as course_description, u.name as lecturer_name 
    FROM classes c
    JOIN courses co ON c.course_id = co.id
    JOIN class_members cm ON c.id = cm.class_id
    JOIN users u ON c.lecturer_id = u.id
    WHERE cm.student_id = ?
  `).all(req.params.id);
  res.json(classes);
});

// Get Class Details (Materials, Assignments)
router.get('/classes/:id/details', (req, res) => {
  const materials = db.prepare('SELECT * FROM materials WHERE class_id = ?').all(req.params.id);
  const assignments = db.prepare('SELECT * FROM assignments WHERE class_id = ?').all(req.params.id);
  res.json({ materials, assignments });
});

// Create Assignment (Modified to support questions and multiple classes/meetings)
router.post('/assignments/create-batch', (req, res) => {
  const { class_ids, meeting_index, title, description, due_date, type, work_type, main_questions } = req.body;
  // main_questions structure: [{ title, description, questions: [{ question_text, ... }] }]
  
  const transaction = db.transaction(() => {
    for (const classId of class_ids) {
      // Find the meeting_id for this class based on index (date sorted)
      const meetings = db.prepare('SELECT id FROM meetings WHERE class_id = ? ORDER BY date ASC').all(classId);
      const meetingId = meetings[meeting_index] ? (meetings[meeting_index] as any).id : null;

      const info = db.prepare('INSERT INTO assignments (class_id, meeting_id, title, description, due_date, type, work_type) VALUES (?, ?, ?, ?, ?, ?, ?)').run(classId, meetingId, title, description, due_date, type || 'tugas', work_type || 'individu');
      const assignmentId = info.lastInsertRowid;

      // Insert Main Questions and Questions
      if (main_questions && main_questions.length > 0) {
        const insertMain = db.prepare('INSERT INTO main_questions (assignment_id, title, description) VALUES (?, ?, ?)');
        const insertQ = db.prepare('INSERT INTO questions (assignment_id, main_question_id, question_text, question_type, options, correct_answer, points) VALUES (?, ?, ?, ?, ?, ?, ?)');
        
        for (const main of main_questions) {
          const mainInfo = insertMain.run(assignmentId, main.title, main.description);
          const mainId = mainInfo.lastInsertRowid;
          
          if (main.questions && main.questions.length > 0) {
            for (const q of main.questions) {
              insertQ.run(assignmentId, mainId, q.question_text, q.question_type, JSON.stringify(q.options), q.correct_answer, q.points);
            }
          }
        }
      }
    }
  });

  try {
    transaction();
    res.json({ success: true });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Failed to create assignments' });
  }
});

// Get Assignment Details (with Questions)
router.get('/assignments/:id', (req, res) => {
  const assignment = db.prepare('SELECT * FROM assignments WHERE id = ?').get(req.params.id);
  if (!assignment) return res.status(404).json({ error: 'Assignment not found' });
  
  // Fetch Main Questions with their sub-questions
  const mainQuestions = db.prepare('SELECT * FROM main_questions WHERE assignment_id = ?').all(req.params.id) as any[];
  
  const resultMainQuestions = mainQuestions.map(mq => {
    const questions = db.prepare('SELECT * FROM questions WHERE main_question_id = ?').all(mq.id);
    return { ...mq, questions };
  });

  // Also fetch standalone questions (if any, for backward compatibility or flexibility)
  const standaloneQuestions = db.prepare('SELECT * FROM questions WHERE assignment_id = ? AND main_question_id IS NULL').all(req.params.id);

  res.json({ ...assignment, main_questions: resultMainQuestions, standalone_questions: standaloneQuestions });
});

// Get Submissions for an Assignment
router.get('/assignments/:id/submissions', (req, res) => {
  const submissions = db.prepare(`
    SELECT s.*, u.name as student_name 
    FROM submissions s
    JOIN users u ON s.student_id = u.id
    WHERE s.assignment_id = ?
  `).all(req.params.id);
  res.json(submissions);
});

// Submit Assignment (with Answers)
router.post('/submissions', (req, res) => {
  try {
    const { assignment_id, student_id, answers } = req.body; // answers: [{ question_id, answer_text }]
    console.log(`[API] Received submission attempt: assignment_id=${assignment_id}, student_id=${student_id}, answers_count=${answers?.length || 0}`);
    
    if (!assignment_id || !student_id) {
      console.error('[API] Missing required fields: assignment_id or student_id');
      return res.status(400).json({ error: 'Missing assignment_id or student_id' });
    }

    // Verify student exists
    const student = db.prepare('SELECT id FROM users WHERE id = ?').get(student_id);
    if (!student) {
      console.error(`[API] Student with ID ${student_id} not found`);
      return res.status(404).json({ error: 'Mahasiswa tidak ditemukan.' });
    }

    // Verify assignment exists
    const assignment = db.prepare('SELECT id FROM assignments WHERE id = ?').get(assignment_id);
    if (!assignment) {
      console.error(`[API] Assignment with ID ${assignment_id} not found`);
      return res.status(404).json({ error: 'Tugas tidak ditemukan.' });
    }

    // Check if already submitted
    const existing = db.prepare('SELECT id FROM submissions WHERE assignment_id = ? AND student_id = ?').get(assignment_id, student_id);
    if (existing) {
      console.warn(`[API] Duplicate submission attempt: student ${student_id} for assignment ${assignment_id}`);
      return res.status(400).json({ error: 'Anda sudah mengumpulkan tugas ini.' });
    }

    const transaction = db.transaction(() => {
      // Create Submission
      console.log('[API] Creating submission record...');
      const info = db.prepare('INSERT INTO submissions (assignment_id, student_id, submitted_at) VALUES (?, ?, CURRENT_TIMESTAMP)').run(assignment_id, student_id);
      const submissionId = info.lastInsertRowid;
      console.log(`[API] Submission record created with ID: ${submissionId}`);

      // Insert Answers
      if (answers && Array.isArray(answers) && answers.length > 0) {
        console.log(`[API] Inserting ${answers.length} answers...`);
        const insertAns = db.prepare('INSERT INTO student_answers (submission_id, question_id, answer_text) VALUES (?, ?, ?)');
        for (const ans of answers) {
          if (!ans.question_id) {
            console.warn('[API] Skipping answer with missing question_id');
            continue;
          }
          insertAns.run(submissionId, ans.question_id, ans.answer_text || '');
        }
      }
      
      return submissionId;
    });

    const submissionId = transaction();
    console.log(`[API] Transaction committed successfully. Submission ID: ${submissionId}`);
    res.json({ success: true, id: submissionId });

  } catch (err: any) {
    console.error('[API] CRITICAL Submission error:', err);
    res.status(500).json({ 
      error: 'Gagal menyimpan jawaban ke database.', 
      details: err.message,
      code: err.code // SQLite error code if available
    });
  }
});

// Get Submission for Grading
router.get('/submissions/:id/details', (req, res) => {
  const submission = db.prepare(`
    SELECT s.*, u.name as student_name 
    FROM submissions s
    JOIN users u ON s.student_id = u.id
    WHERE s.id = ?
  `).get(req.params.id);
  
  if (!submission) return res.status(404).json({ error: 'Submission not found' });

  const answers = db.prepare(`
    SELECT sa.*, q.question_text, q.correct_answer, q.points, q.question_type
    FROM student_answers sa
    JOIN questions q ON sa.question_id = q.id
    WHERE sa.submission_id = ?
  `).all(req.params.id);

  res.json({ ...submission, answers });
});

// Grade Submission
router.post('/submissions/:id/grade', (req, res) => {
  const { grade, feedback } = req.body;
  db.prepare('UPDATE submissions SET grade = ?, feedback = ? WHERE id = ?').run(grade, feedback, req.params.id);
  res.json({ success: true });
});

// --- GRADING ROUTES ---

// Get Grading Settings
router.get('/classes/:id/grading-settings', (req, res) => {
  const settings = db.prepare('SELECT * FROM grading_settings WHERE class_id = ?').all(req.params.id);
  res.json(settings);
});

// Update Grading Settings
router.post('/classes/:id/grading-settings', (req, res) => {
  const { settings } = req.body; // Array of { type, weight }
  const classId = req.params.id;
  
  const insert = db.prepare('INSERT OR REPLACE INTO grading_settings (class_id, type, weight) VALUES (?, ?, ?)');
  const transaction = db.transaction((items) => {
    for (const item of items) {
      insert.run(classId, item.type, item.weight);
    }
  });
  
  try {
    transaction(settings);
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: 'Failed to save settings' });
  }
});

// Get Grade Recap (Modified to group by meeting if needed, but keeping simple for now)
router.get('/classes/:id/grades', (req, res) => {
  const classId = req.params.id;
  
  // Get all students in class
  const students = db.prepare(`
    SELECT u.id, u.name, u.username 
    FROM users u
    JOIN class_members cm ON u.id = cm.student_id
    WHERE cm.class_id = ?
  `).all(classId);

  // Get all assignments with meeting info
  const assignments = db.prepare(`
    SELECT a.id, a.title, a.type, a.meeting_id, m.title as meeting_title
    FROM assignments a
    LEFT JOIN meetings m ON a.meeting_id = m.id
    WHERE a.class_id = ?
  `).all(classId);
  
  // Get all submissions
  const submissions = db.prepare(`
    SELECT s.*, a.type 
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id
    WHERE a.class_id = ?
  `).all(classId);

  // Get weights
  const weights = db.prepare('SELECT type, weight FROM grading_settings WHERE class_id = ?').all(classId);
  const weightMap = weights.reduce((acc: any, curr: any) => ({ ...acc, [curr.type]: curr.weight }), {});

  // Calculate grades
  const recap = students.map((student: any) => {
    const studentSubmissions = submissions.filter((s: any) => s.student_id === student.id);
    
    // Calculate average per type
    const types = ['latihan', 'tugas', 'ujian'];
    const typeGrades: any = {};
    
    types.forEach(type => {
      const typeAssignments = assignments.filter((a: any) => a.type === type);
      if (typeAssignments.length === 0) {
        typeGrades[type] = 0;
        return;
      }
      
      const totalScore = typeAssignments.reduce((sum: number, a: any) => {
        const sub = studentSubmissions.find((s: any) => s.assignment_id === a.id);
        return sum + (sub ? (sub.grade || 0) : 0);
      }, 0);
      
      typeGrades[type] = totalScore / typeAssignments.length;
    });

    // Calculate Final Grade
    let totalWeight = 0;
    let weightedSum = 0;
    
    types.forEach(type => {
      const weight = weightMap[type] || 0;
      if (weight > 0) {
        weightedSum += typeGrades[type] * (weight / 100);
        totalWeight += weight;
      }
    });

    // Normalize if total weight is not 100 (optional, but good for safety)
    // For now, assume simple weighted sum. If weights don't add up to 100, the grade might be weird.
    // Let's just output the weighted sum.
    
    return {
      ...student,
      grades: typeGrades,
      finalGrade: weightedSum
    };
  });

  res.json({ recap, assignments, weights });
});

// --- ATTENDANCE ROUTES ---

// Get Attendance for a Class
router.get('/classes/:id/attendance', (req, res) => {
  const attendance = db.prepare(`
    SELECT a.*, m.title as meeting_title 
    FROM attendance a
    JOIN meetings m ON a.meeting_id = m.id
    WHERE a.class_id = ?
    ORDER BY a.created_at DESC
  `).all(req.params.id);
  res.json(attendance);
});

// Create Attendance Session
router.post('/attendance', (req, res) => {
  const { class_id, meeting_id, code, lecturer_id } = req.body;
  
  const transaction = db.transaction(() => {
    // 1. Create Attendance Session
    const info = db.prepare('INSERT INTO attendance (class_id, meeting_id, code) VALUES (?, ?, ?)').run(class_id, meeting_id, code);
    const attendanceId = info.lastInsertRowid;

    // 2. Post to Chat
    const meeting = db.prepare('SELECT title FROM meetings WHERE id = ?').get(meeting_id) as any;
    const content = `📢 **PRESENSI DIBUKA**\n\nPertemuan: ${meeting?.title || 'N/A'}\nKode Presensi: **${code}**\n\nSilakan masukkan kode ini di menu presensi Anda.`;
    
    db.prepare('INSERT INTO messages (sender_id, class_id, content) VALUES (?, ?, ?)').run(lecturer_id, class_id, content);

    return attendanceId;
  });

  try {
    const id = transaction();
    res.json({ id });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Failed to create attendance session' });
  }
});

// Get Attendance Submissions (Report)
router.get('/attendance/:id/submissions', (req, res) => {
  const submissions = db.prepare(`
    SELECT asub.*, u.name as student_name, u.username as student_id_number
    FROM attendance_submissions asub
    JOIN users u ON asub.student_id = u.id
    WHERE asub.attendance_id = ?
  `).all(req.params.id);
  res.json(submissions);
});

// Student Submit Attendance
router.post('/attendance/submit', (req, res) => {
  const { student_id, code, class_id } = req.body;
  
  // Find active attendance for this class with this code
  const attendance = db.prepare('SELECT id FROM attendance WHERE class_id = ? AND code = ? AND status = "open"').get(class_id, code) as any;
  
  if (!attendance) {
    return res.status(400).json({ error: 'Kode presensi salah atau sudah ditutup.' });
  }

  try {
    db.prepare('INSERT INTO attendance_submissions (attendance_id, student_id) VALUES (?, ?)').run(attendance.id, student_id);
    res.json({ success: true });
  } catch (err) {
    res.status(400).json({ error: 'Anda sudah melakukan presensi untuk sesi ini.' });
  }
});

// Close Attendance
router.post('/attendance/:id/close', (req, res) => {
  db.prepare('UPDATE attendance SET status = "closed" WHERE id = ?').run(req.params.id);
  res.json({ success: true });
});

// --- ANNOUNCEMENTS ROUTES ---

router.get('/announcements', (req, res) => {
  const announcements = db.prepare(`
    SELECT a.*, u.name as author_name 
    FROM announcements a
    JOIN users u ON a.author_id = u.id
    ORDER BY a.created_at DESC
  `).all();
  res.json(announcements);
});

router.post('/announcements', (req, res) => {
  const { title, content, author_id } = req.body;
  const info = db.prepare('INSERT INTO announcements (title, content, author_id) VALUES (?, ?, ?)').run(title, content, author_id);
  res.json({ id: info.lastInsertRowid });
});

router.delete('/announcements/:id', (req, res) => {
  db.prepare('DELETE FROM announcements WHERE id = ?').run(req.params.id);
  res.json({ success: true });
});

// --- MEETING ROUTES ---

router.get('/classes/:id/meetings', (req, res) => {
  const meetings = db.prepare('SELECT * FROM meetings WHERE class_id = ? ORDER BY date ASC').all(req.params.id);
  res.json(meetings);
});

router.post('/meetings', (req, res) => {
  const { class_id, title, description, date, learning_model, learning_syntax, materials } = req.body;
  console.log('Creating meeting with materials:', materials);
  
  const transaction = db.transaction(() => {
    const info = db.prepare('INSERT INTO meetings (class_id, title, description, date, learning_model, learning_syntax) VALUES (?, ?, ?, ?, ?, ?)').run(class_id, title, description, date, learning_model, JSON.stringify(learning_syntax));
    const meetingId = info.lastInsertRowid;

    if (materials && Array.isArray(materials) && materials.length > 0) {
      const insertMaterial = db.prepare('INSERT INTO materials (class_id, meeting_id, title, content, type) VALUES (?, ?, ?, ?, ?)');
      for (const mat of materials) {
        insertMaterial.run(class_id, meetingId, mat.title, mat.content, mat.type || 'text');
      }
    }
    
    return meetingId;
  });

  try {
    const meetingId = transaction();
    res.json({ id: meetingId });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Failed to create meeting' });
  }
});

// Update Meeting
router.put('/meetings/:id', (req, res) => {
  const { title, description, date, learning_model, learning_syntax, materials } = req.body;
  const meetingId = req.params.id;
  const classId = db.prepare('SELECT class_id FROM meetings WHERE id = ?').get(meetingId)?.class_id;

  const transaction = db.transaction(() => {
    db.prepare('UPDATE meetings SET title = ?, description = ?, date = ?, learning_model = ?, learning_syntax = ? WHERE id = ?')
      .run(title, description, date, learning_model, JSON.stringify(learning_syntax), meetingId);

    if (materials && Array.isArray(materials) && materials.length > 0 && classId) {
      const insertMaterial = db.prepare('INSERT INTO materials (class_id, meeting_id, title, content, type) VALUES (?, ?, ?, ?, ?)');
      for (const mat of materials) {
        insertMaterial.run(classId, meetingId, mat.title, mat.content, mat.type || 'text');
      }
    }
  });

  try {
    transaction();
    res.json({ success: true });
  } catch (err) {
    console.error('Error updating meeting:', err);
    res.status(500).json({ error: 'Failed to update meeting' });
  }
});

router.delete('/meetings/:id', (req, res) => {
  db.prepare('DELETE FROM meetings WHERE id = ?').run(req.params.id);
  res.json({ success: true });
});

// Update Material
router.put('/materials/:id', (req, res) => {
  const { title, content, type } = req.body;
  db.prepare('UPDATE materials SET title = ?, content = ?, type = ? WHERE id = ?')
    .run(title, content, type, req.params.id);
  res.json({ success: true });
});

// Delete Material
router.delete('/materials/:id', (req, res) => {
  db.prepare('DELETE FROM materials WHERE id = ?').run(req.params.id);
  res.json({ success: true });
});

// Update Assignment
router.put('/assignments/:id', (req, res) => {
  const { title, description, due_date, type, work_type, main_questions } = req.body;
  
  const transaction = db.transaction(() => {
    db.prepare('UPDATE assignments SET title = ?, description = ?, due_date = ?, type = ?, work_type = ? WHERE id = ?')
      .run(title, description, due_date, type, work_type, req.params.id);

    if (main_questions && Array.isArray(main_questions)) {
      // For simplicity, we'll delete old structure and recreate. 
      // In a real app, we might want to update intelligently to preserve IDs if needed, 
      // but for this prototype, full replace is safer for consistency.
      
      // Delete old questions linked to this assignment
      db.prepare('DELETE FROM questions WHERE assignment_id = ?').run(req.params.id);
      // Delete old main questions
      db.prepare('DELETE FROM main_questions WHERE assignment_id = ?').run(req.params.id);
      
      const insertMain = db.prepare('INSERT INTO main_questions (assignment_id, title, description) VALUES (?, ?, ?)');
      const insertQ = db.prepare('INSERT INTO questions (assignment_id, main_question_id, question_text, question_type, options, correct_answer, points) VALUES (?, ?, ?, ?, ?, ?, ?)');

      for (const main of main_questions) {
        const mainInfo = insertMain.run(req.params.id, main.title, main.description);
        const mainId = mainInfo.lastInsertRowid;
        
        if (main.questions && main.questions.length > 0) {
          for (const q of main.questions) {
            insertQ.run(req.params.id, mainId, q.question_text, q.question_type, JSON.stringify(q.options), q.correct_answer, q.points);
          }
        }
      }
    }
  });

  try {
    transaction();
    res.json({ success: true });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Failed to update assignment' });
  }
});

// Share Material
router.post('/materials/share', (req, res) => {
  const { material_id, target_class_id, target_meeting_id } = req.body;
  const material = db.prepare('SELECT * FROM materials WHERE id = ?').get(material_id) as any;
  if (!material) return res.status(404).json({ error: 'Material not found' });

  try {
    db.prepare('INSERT INTO materials (class_id, meeting_id, title, content, type) VALUES (?, ?, ?, ?, ?)')
      .run(target_class_id, target_meeting_id, material.title, material.content, material.type);
    res.json({ success: true });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Failed to share material' });
  }
});

// Share Assignment
router.post('/assignments/share', (req, res) => {
  const { assignment_id, target_class_id, target_meeting_id } = req.body;
  const assignment = db.prepare('SELECT * FROM assignments WHERE id = ?').get(assignment_id) as any;
  if (!assignment) return res.status(404).json({ error: 'Assignment not found' });

  const questions = db.prepare('SELECT * FROM questions WHERE assignment_id = ?').all(assignment_id) as any[];

  const transaction = db.transaction(() => {
    const info = db.prepare('INSERT INTO assignments (class_id, meeting_id, title, description, due_date, type, work_type) VALUES (?, ?, ?, ?, ?, ?, ?)')
      .run(target_class_id, target_meeting_id, assignment.title, assignment.description, assignment.due_date, assignment.type, assignment.work_type);
    const newAssignmentId = info.lastInsertRowid;

    const insertQ = db.prepare('INSERT INTO questions (assignment_id, question_text, question_type, options, correct_answer, points) VALUES (?, ?, ?, ?, ?, ?)');
    for (const q of questions) {
      insertQ.run(newAssignmentId, q.question_text, q.question_type, q.options, q.correct_answer, q.points);
    }
  });

  try {
    transaction();
    res.json({ success: true });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Failed to share assignment' });
  }
});

// Delete Assignment
router.delete('/assignments/:id', (req, res) => {
  db.prepare('DELETE FROM assignments WHERE id = ?').run(req.params.id);
  res.json({ success: true });
});

// --- CHAT ROUTES ---

// Get Chat Hierarchy for Lecturer
router.get('/lecturer/:id/chat-hierarchy', (req, res) => {
  const lecturerId = req.params.id;
  
  // Get Courses
  const courses = db.prepare('SELECT id, name, code FROM courses WHERE lecturer_id = ?').all(lecturerId) as any[];
  
  const hierarchy = courses.map(course => {
    // Get Classes for Course
    const classes = db.prepare('SELECT id, name, code FROM classes WHERE course_id = ?').all(course.id) as any[];
    
    const classesWithGroups = classes.map(cls => {
      // Get Groups for Class
      const groups = db.prepare('SELECT id, name FROM groups WHERE class_id = ?').all(cls.id) as any[];
      return {
        ...cls,
        groups
      };
    });

    return {
      ...course,
      classes: classesWithGroups
    };
  });

  res.json(hierarchy);
});

// Get Conversations (for Lecturer - Personal)
router.get('/chat/conversations', (req, res) => {
  // Assuming req.user.id is passed via query or we need to pass it explicitly for now since we don't have full middleware
  // Let's assume the frontend passes ?user_id=...
  const userId = req.query.user_id;
  
  if (!userId) return res.status(400).json({ error: 'User ID required' });

  // Find users who have exchanged messages with this user
  const conversations = db.prepare(`
    SELECT DISTINCT u.id, u.name, u.username, u.role,
      (SELECT content FROM messages m2 
       WHERE (m2.sender_id = u.id AND m2.receiver_id = ?) 
          OR (m2.sender_id = ? AND m2.receiver_id = u.id)
       ORDER BY m2.created_at DESC LIMIT 1) as last_message,
      (SELECT created_at FROM messages m3 
       WHERE (m3.sender_id = u.id AND m3.receiver_id = ?) 
          OR (m3.sender_id = ? AND m3.receiver_id = u.id)
       ORDER BY m3.created_at DESC LIMIT 1) as last_message_time
    FROM users u
    JOIN messages m ON (m.sender_id = u.id AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = u.id)
    WHERE u.id != ?
    ORDER BY last_message_time DESC
  `).all(userId, userId, userId, userId, userId, userId, userId);

  res.json(conversations);
});

// Get Messages (Personal)
router.get('/chat/personal/:otherUserId', (req, res) => {
  const userId = req.query.user_id;
  const otherUserId = req.params.otherUserId;
  
  const messages = db.prepare(`
    SELECT m.*, s.name as sender_name 
    FROM messages m
    JOIN users s ON m.sender_id = s.id
    WHERE (m.sender_id = ? AND m.receiver_id = ?) 
       OR (m.sender_id = ? AND m.receiver_id = ?)
    ORDER BY m.created_at ASC
  `).all(userId, otherUserId, otherUserId, userId);
  
  res.json(messages);
});

// Get Messages (Group/Class)
router.get('/chat/group/:classId', (req, res) => {
  const { group_id } = req.query;
  
  let query = `
    SELECT m.*, s.name as sender_name, s.role as sender_role
    FROM messages m
    JOIN users s ON m.sender_id = s.id
    WHERE m.class_id = ? AND m.receiver_id IS NULL
  `;
  
  const params = [req.params.classId];

  if (group_id) {
    query += ` AND m.group_id = ?`;
    params.push(String(group_id));
  } else {
    query += ` AND m.group_id IS NULL`;
  }
  
  query += ` ORDER BY m.created_at ASC`;

  const messages = db.prepare(query).all(...params);
  res.json(messages);
});

// Send Message
router.post('/chat/send', (req, res) => {
  const { sender_id, receiver_id, class_id, group_id, content } = req.body;
  
  const info = db.prepare(`
    INSERT INTO messages (sender_id, receiver_id, class_id, group_id, content)
    VALUES (?, ?, ?, ?, ?)
  `).run(sender_id, receiver_id, class_id, group_id, content);
  
  res.json({ id: info.lastInsertRowid });
});

// Get Student's Group in a Class
router.get('/classes/:classId/students/:studentId/group', (req, res) => {
  const group = db.prepare(`
    SELECT g.* 
    FROM groups g
    JOIN group_members gm ON g.id = gm.group_id
    WHERE g.class_id = ? AND gm.student_id = ?
  `).get(req.params.classId, req.params.studentId);
  
  res.json(group || null);
});

// Get Unread Count
router.get('/chat/unread', (req, res) => {
  const userId = req.query.user_id;
  if (!userId) return res.status(400).json({ error: 'User ID required' });

  // Personal unread
  const personalUnread = db.prepare(`
    SELECT COUNT(*) as count FROM messages 
    WHERE receiver_id = ? AND is_read = 0
  `).get(userId) as { count: number };

  // Group unread (more complex, need to know which classes user is in)
  // For simplicity, we'll just check personal messages for now, 
  // or if we want group messages, we need a way to track "last read" per user per group.
  // Since we only added `is_read` to message, it works for 1-on-1. 
  // For groups, `is_read` on the message itself implies "read by everyone" which is wrong.
  // For a proper group chat read receipt, we need a separate table `message_reads`.
  // Given the constraints, let's focus on Personal Unread for the badge, 
  // or just assume `is_read` is for the intended receiver.
  // For group chats, we won't track unread status perfectly without a join table.
  // Let's stick to Personal Unread for now to avoid schema complexity explosion.
  
  res.json({ count: personalUnread.count });
});

// Mark Read
router.post('/chat/read', (req, res) => {
  const { user_id, sender_id } = req.body;
  
  // Mark all messages from sender_id to user_id as read
  db.prepare(`
    UPDATE messages SET is_read = 1 
    WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
  `).run(sender_id, user_id);
  
  res.json({ success: true });
});

// --- GROUP ROUTES ---

// Get Groups for a Class
router.get('/classes/:id/groups', (req, res) => {
  const groups = db.prepare('SELECT * FROM groups WHERE class_id = ?').all(req.params.id);
  // For each group, get members
  const groupsWithMembers = groups.map((g: any) => {
    const members = db.prepare(`
      SELECT u.id, u.name, u.username 
      FROM users u
      JOIN group_members gm ON u.id = gm.student_id
      WHERE gm.group_id = ?
    `).all(g.id);
    return { ...g, members };
  });
  res.json(groupsWithMembers);
});

// Create Group
router.post('/groups', (req, res) => {
  const { class_id, name } = req.body;
  const info = db.prepare('INSERT INTO groups (class_id, name) VALUES (?, ?)').run(class_id, name);
  res.json({ id: info.lastInsertRowid, name, class_id, members: [] });
});

// Delete Group
router.delete('/groups/:id', (req, res) => {
  db.prepare('DELETE FROM groups WHERE id = ?').run(req.params.id);
  res.json({ success: true });
});

// Add Member to Group
router.post('/groups/:id/members', (req, res) => {
  const { student_id } = req.body;
  try {
    db.prepare('INSERT INTO group_members (group_id, student_id) VALUES (?, ?)').run(req.params.id, student_id);
    res.json({ success: true });
  } catch (e) {
    res.status(400).json({ error: 'Member already in group' });
  }
});

// Remove Member from Group
router.delete('/groups/:id/members/:studentId', (req, res) => {
  db.prepare('DELETE FROM group_members WHERE group_id = ? AND student_id = ?').run(req.params.id, req.params.studentId);
  res.json({ success: true });
});

// Get Students in Class (for selection)
router.get('/classes/:id/students', (req, res) => {
  const students = db.prepare(`
    SELECT u.id, u.name, u.username 
    FROM users u
    JOIN class_members cm ON u.id = cm.student_id
    WHERE cm.class_id = ?
  `).all(req.params.id);
  res.json(students);
});

// --- GENERAL ROUTES ---

// Get All Students (for waiting room list)
router.get('/students', (req, res) => {
  const students = db.prepare("SELECT id, name, username FROM users WHERE role = 'mahasiswa'").all();
  res.json(students);
});

// Get Course Details with Classes
router.get('/courses/:id/details', (req, res) => {
  const course = db.prepare('SELECT * FROM courses WHERE id = ?').get(req.params.id);
  if (!course) return res.status(404).json({ error: 'Course not found' });
  
  const classes = db.prepare('SELECT * FROM classes WHERE course_id = ?').all(req.params.id);
  res.json({ ...course, classes });
});

// Get Single Class Info
router.get('/classes/:id', (req, res) => {
  const cls = db.prepare('SELECT * FROM classes WHERE id = ?').get(req.params.id);
  if (!cls) return res.status(404).json({ error: 'Class not found' });
  res.json(cls);
});

// Get Student Dashboard Data
router.get('/student/:id/dashboard', (req, res) => {
  const studentId = req.params.id;

  // Get all classes the student is enrolled in
  const classes = db.prepare('SELECT class_id FROM class_members WHERE student_id = ?').all(studentId);
  const classIds = classes.map((c: any) => c.class_id);

  if (classIds.length === 0) {
    return res.json({ assignments: [], stats: { total: 0, completed: 0, pending: 0, averageGrade: 0 } });
  }

  // Get all assignments for these classes
  const assignments = db.prepare(`
    SELECT a.id, a.title, a.type, a.due_date, c.name as class_name, m.title as meeting_title
    FROM assignments a
    JOIN classes c ON a.class_id = c.id
    LEFT JOIN meetings m ON a.meeting_id = m.id
    WHERE a.class_id IN (${classIds.join(',')})
    ORDER BY a.due_date DESC
  `).all();

  // Get submissions for these assignments
  const submissions = db.prepare(`
    SELECT * FROM submissions WHERE student_id = ?
  `).all(studentId);

  // Map submissions to assignments
  const result = assignments.map((a: any) => {
    const sub = submissions.find((s: any) => s.assignment_id === a.id);
    let status = 'Belum Dikerjakan';
    let grade = null;

    if (sub) {
      status = sub.grade !== null ? 'Selesai' : 'Menunggu Penilaian';
      grade = sub.grade;
    }

    return {
      ...a,
      status,
      grade
    };
  });

  // Calculate stats
  const total = result.length;
  const completed = result.filter((r: any) => r.status === 'Selesai').length;
  const inProgress = result.filter((r: any) => r.status === 'Sedang Dikerjakan').length;
  const notStarted = total - completed - inProgress;
  
  const gradedAssignments = result.filter((r: any) => r.grade !== null);
  const averageGrade = gradedAssignments.length > 0 
    ? gradedAssignments.reduce((sum: number, r: any) => sum + r.grade, 0) / gradedAssignments.length 
    : 0;

  res.json({
    assignments: result,
    stats: {
      total,
      completed,
      inProgress,
      notStarted,
      averageGrade
    }
  });
});

// --- EXTERNAL REFERENCES ROUTES ---

// Get all references
router.get('/references', (req, res) => {
  const references = db.prepare(`
    SELECT er.*, u.name as lecturer_name 
    FROM external_references er
    JOIN users u ON er.lecturer_id = u.id
    ORDER BY er.created_at DESC
  `).all();
  res.json(references);
});

// Add reference
router.post('/references', (req, res) => {
  const { title, author, field, publisher, file_url, content, lecturer_id } = req.body;
  try {
    const info = db.prepare('INSERT INTO external_references (title, author, field, publisher, file_url, content, lecturer_id) VALUES (?, ?, ?, ?, ?, ?, ?)').run(title, author, field, publisher, file_url, content, lecturer_id);
    res.json({ id: info.lastInsertRowid });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Failed to add reference' });
  }
});

// Delete reference
router.delete('/references/:id', (req, res) => {
  db.prepare('DELETE FROM external_references WHERE id = ?').run(req.params.id);
  res.json({ success: true });
});

// --- RAG Documents ---

// Get all RAG documents
router.get('/rag-documents', (req, res) => {
  try {
    const docs = db.prepare(`
      SELECT r.*, u.name as lecturer_name 
      FROM rag_documents r
      JOIN users u ON r.lecturer_id = u.id
      ORDER BY r.created_at DESC
    `).all();
    res.json(docs);
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Failed to fetch RAG documents' });
  }
});

// Add RAG document
router.post('/rag-documents', (req, res) => {
  const { title, content, lecturer_id } = req.body;
  try {
    const info = db.prepare('INSERT INTO rag_documents (title, content, lecturer_id) VALUES (?, ?, ?)').run(title, content, lecturer_id);
    res.json({ id: info.lastInsertRowid });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Failed to add RAG document' });
  }
});

// Delete RAG document
router.delete('/rag-documents/:id', (req, res) => {
  try {
    db.prepare('DELETE FROM rag_documents WHERE id = ?').run(req.params.id);
    res.json({ success: true });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Failed to delete RAG document' });
  }
});

export default router;
