import express from 'express';
import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';
import { db } from '../../db/database';

const router = express.Router();
const JWT_SECRET = process.env.JWT_SECRET || 'secret-key-change-me';

// Register
router.post('/register', async (req, res) => {
  const { username, password, role, name } = req.body;
  
  if (!['admin', 'dosen', 'mahasiswa'].includes(role)) {
    return res.status(400).json({ error: 'Invalid role' });
  }

  try {
    const hashedPassword = await bcrypt.hash(password, 10);
    const stmt = db.prepare('INSERT INTO users (username, password, role, name) VALUES (?, ?, ?, ?)');
    const info = stmt.run(username, hashedPassword, role, name);
    
    res.json({ id: info.lastInsertRowid, username, role, name });
  } catch (error: any) {
    res.status(400).json({ error: 'Username already exists or invalid data' });
  }
});

// Login
router.post('/login', async (req, res) => {
  const { username, password } = req.body;
  
  const user: any = db.prepare('SELECT * FROM users WHERE username = ?').get(username);
  
  if (!user) {
    return res.status(400).json({ error: 'User not found' });
  }

  const validPassword = await bcrypt.compare(password, user.password);
  if (!validPassword) {
    return res.status(400).json({ error: 'Invalid password' });
  }

  const token = jwt.sign({ id: user.id, role: user.role, name: user.name }, JWT_SECRET, { expiresIn: '24h' });
  
  res.cookie('token', token, { httpOnly: true, secure: process.env.NODE_ENV === 'production' });
  res.json({ id: user.id, username: user.username, role: user.role, name: user.name });
});

// Logout
router.post('/logout', (req, res) => {
  res.clearCookie('token');
  res.json({ message: 'Logged out' });
});

// Get Current User
router.get('/me', (req, res) => {
  const token = req.cookies.token;
  if (!token) return res.status(401).json({ error: 'Not authenticated' });

  try {
    const decoded = jwt.verify(token, JWT_SECRET);
    res.json(decoded);
  } catch (error) {
    res.status(401).json({ error: 'Invalid token' });
  }
});

export default router;
