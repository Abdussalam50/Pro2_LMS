import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import { ReferenceProvider } from './context/ReferenceContext';
import Layout from './components/Layout';
import RequireAuth from './components/RequireAuth';
import Login from './pages/Login';
import Register from './pages/Register';
import Dashboard from './pages/Dashboard';
import UserManagement from './pages/admin/UserManagement';
import LecturerCourses from './pages/lecturer/LecturerCourses';
import LecturerCourseDetail from './pages/lecturer/LecturerCourseDetail';
import LecturerClassDetail from './pages/lecturer/LecturerClassDetail';
import LecturerGrading from './pages/lecturer/LecturerGrading';
import LecturerCreateAssignment from './pages/lecturer/LecturerCreateAssignment';
import LecturerChat from './pages/lecturer/LecturerChat';
import LecturerGroups from './pages/lecturer/LecturerGroups';
import LecturerAssignmentSubmissions from './pages/lecturer/LecturerAssignmentSubmissions';
import LecturerAttendance from './pages/lecturer/LecturerAttendance';
import StudentClasses from './pages/student/StudentClasses';
import StudentChat from './pages/student/StudentChat';
import ClassDetail from './pages/student/ClassDetail';
import StudentDoAssignment from './pages/student/StudentDoAssignment';

export default function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <ReferenceProvider>
          <Routes>
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />
            
            <Route element={<RequireAuth><Layout /></RequireAuth>}>
              <Route path="/dashboard" element={<Dashboard />} />
              <Route path="/admin/users" element={<UserManagement />} />
              
              <Route path="/lecturer/courses" element={<LecturerCourses />} />
              <Route path="/lecturer/courses/:courseId" element={<LecturerCourseDetail />} />
              <Route path="/lecturer/classes/:classId" element={<LecturerClassDetail />} />
              <Route path="/lecturer/grading" element={<LecturerGrading />} />
              <Route path="/lecturer/attendance" element={<LecturerAttendance />} />
              <Route path="/lecturer/create-assignment" element={<LecturerCreateAssignment />} />
              <Route path="/lecturer/assignments/:assignmentId/submissions" element={<LecturerAssignmentSubmissions />} />
              <Route path="/lecturer/groups" element={<LecturerGroups />} />
              <Route path="/lecturer/chat" element={<LecturerChat />} />

              <Route path="/student/classes" element={<StudentClasses />} />
              <Route path="/student/classes/:id" element={<ClassDetail />} />
              <Route path="/student/assignments/:id" element={<StudentDoAssignment />} />
              <Route path="/student/chat" element={<StudentChat />} />
            </Route>

            <Route path="*" element={<Navigate to="/login" replace />} />
          </Routes>
        </ReferenceProvider>
      </AuthProvider>
    </BrowserRouter>
  );
}
