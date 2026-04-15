import React, { useState } from 'react';
import { Outlet, Link, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useReference } from '../context/ReferenceContext';
import { LogOut, User, BookOpen, Users, Home, Award, CheckSquare, MessageCircle, Menu, X, Calendar, Book, Bot, Database } from 'lucide-react';
import ReferenceModal from './ReferenceModal';

export default function Layout() {
  const { user, logout } = useAuth();
  const { openRAGChatbot, openRAGDocumentModal, openReferenceModal } = useReference();
  const location = useLocation();
  const [isSidebarOpen, setIsSidebarOpen] = useState(false);

  const isActive = (path: string) => location.pathname === path ? 'bg-indigo-700' : '';

  const [unreadCount, setUnreadCount] = React.useState(0);

  React.useEffect(() => {
    if (user) {
      fetchUnread();
      const interval = setInterval(fetchUnread, 5000);
      return () => clearInterval(interval);
    }
  }, [user]);

  const fetchUnread = async () => {
    try {
      const res = await fetch(`/api/chat/unread?user_id=${user?.id}`);
      if (res.ok) {
        const data = await res.json();
        setUnreadCount(data.count);
      }
    } catch (e) {
      console.error(e);
    }
  };

  const toggleSidebar = () => setIsSidebarOpen(!isSidebarOpen);
  const closeSidebar = () => setIsSidebarOpen(false);

  return (
    <div className="min-h-screen bg-gray-50 flex font-sans">
      {/* Mobile Header / Menu Button */}
      <div className="md:hidden fixed top-0 left-0 right-0 h-16 bg-white/80 backdrop-blur-md border-b border-gray-200 z-40 flex items-center justify-between px-4 shadow-sm">
        <div className="flex items-center gap-2">
          <div className="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center shadow-sm">
            <BookOpen size={18} className="text-white" />
          </div>
          <span className="font-bold text-gray-800 text-lg tracking-tight">LMS Pro</span>
        </div>
        <button 
          onClick={toggleSidebar}
          className="p-2 rounded-lg hover:bg-gray-100 text-gray-600 transition active:scale-95"
        >
          {isSidebarOpen ? <X size={24} /> : <Menu size={24} />}
        </button>
      </div>

      {/* Overlay for mobile */}
      <div 
        className={`fixed inset-0 bg-black/40 backdrop-blur-sm z-40 md:hidden transition-opacity duration-300 ${isSidebarOpen ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'}`}
        onClick={closeSidebar}
      />

      {/* Sidebar */}
      <aside className={`
        fixed inset-y-0 left-0 z-50 w-72 bg-[#1e1b4b] text-white flex flex-col shadow-2xl transition-transform duration-300 ease-out
        md:static md:translate-x-0 md:shadow-none
        ${isSidebarOpen ? 'translate-x-0' : '-translate-x-full'}
      `}>
        <div className="p-6 flex items-center justify-between border-b border-[#312e81]">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 bg-indigo-500 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-900/50">
              <BookOpen size={24} className="text-white" />
            </div>
            <div>
              <div className="text-xl font-bold tracking-tight">LMS Pro</div>
              <div className="text-xs text-indigo-300 font-medium tracking-wide">Learning System</div>
            </div>
          </div>
          {/* Close button for mobile inside sidebar */}
          <button onClick={closeSidebar} className="md:hidden text-indigo-300 hover:text-white transition p-1 rounded-md hover:bg-[#312e81]">
            <X size={20} />
          </button>
        </div>
        
        <nav className="flex-1 p-4 space-y-1 overflow-y-auto">
          <div className="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-2 px-3 mt-4">Menu Utama</div>
          
          <Link 
            to="/dashboard" 
            onClick={closeSidebar}
            className={`flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 group ${isActive('/dashboard') ? 'bg-indigo-600 text-white shadow-md' : 'text-indigo-100 hover:bg-[#312e81] hover:text-white'}`}
          >
            <Home size={18} className={isActive('/dashboard') ? 'text-white' : 'text-indigo-300 group-hover:text-white'} />
            <span className="font-medium text-sm">Dashboard</span>
          </Link>
          
          {user.role === 'admin' && (
            <>
              <div className="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-2 px-3 mt-6">Administrator</div>
              <Link 
                to="/admin/users" 
                onClick={closeSidebar}
                className={`flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 group ${isActive('/admin/users') ? 'bg-indigo-600 text-white shadow-md' : 'text-indigo-100 hover:bg-[#312e81] hover:text-white'}`}
              >
                <Users size={18} className={isActive('/admin/users') ? 'text-white' : 'text-indigo-300 group-hover:text-white'} />
                <span className="font-medium text-sm">Manajemen User</span>
              </Link>
            </>
          )}

          {user.role === 'dosen' && (
            <>
              <div className="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-2 px-3 mt-6">Akademik</div>
              <Link 
                to="/lecturer/courses" 
                onClick={closeSidebar}
                className={`flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 group ${isActive('/lecturer/courses') ? 'bg-indigo-600 text-white shadow-md' : 'text-indigo-100 hover:bg-[#312e81] hover:text-white'}`}
              >
                <BookOpen size={18} className={isActive('/lecturer/courses') ? 'text-white' : 'text-indigo-300 group-hover:text-white'} />
                <span className="font-medium text-sm">Kelas Saya</span>
              </Link>
              <Link 
                to="/lecturer/grading" 
                onClick={closeSidebar}
                className={`flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 group ${isActive('/lecturer/grading') ? 'bg-indigo-600 text-white shadow-md' : 'text-indigo-100 hover:bg-[#312e81] hover:text-white'}`}
              >
                <Award size={18} className={isActive('/lecturer/grading') ? 'text-white' : 'text-indigo-300 group-hover:text-white'} />
                <span className="font-medium text-sm">Penilaian</span>
              </Link>
              <Link 
                to="/lecturer/attendance" 
                onClick={closeSidebar}
                className={`flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 group ${isActive('/lecturer/attendance') ? 'bg-indigo-600 text-white shadow-md' : 'text-indigo-100 hover:bg-[#312e81] hover:text-white'}`}
              >
                <Calendar size={18} className={isActive('/lecturer/attendance') ? 'text-white' : 'text-indigo-300 group-hover:text-white'} />
                <span className="font-medium text-sm">Presensi</span>
              </Link>
              <Link 
                to="/lecturer/create-assignment" 
                onClick={closeSidebar}
                className={`flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 group ${isActive('/lecturer/create-assignment') ? 'bg-indigo-600 text-white shadow-md' : 'text-indigo-100 hover:bg-[#312e81] hover:text-white'}`}
              >
                <CheckSquare size={18} className={isActive('/lecturer/create-assignment') ? 'text-white' : 'text-indigo-300 group-hover:text-white'} />
                <span className="font-medium text-sm">Input Soal</span>
              </Link>
              <Link 
                to="/lecturer/groups" 
                onClick={closeSidebar}
                className={`flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 group ${isActive('/lecturer/groups') ? 'bg-indigo-600 text-white shadow-md' : 'text-indigo-100 hover:bg-[#312e81] hover:text-white'}`}
              >
                <Users size={18} className={isActive('/lecturer/groups') ? 'text-white' : 'text-indigo-300 group-hover:text-white'} />
                <span className="font-medium text-sm">Kelompok</span>
              </Link>
              
              <div className="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-2 px-3 mt-6">Komunikasi</div>
              <Link 
                to="/lecturer/chat" 
                onClick={closeSidebar}
                className={`flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 group ${isActive('/lecturer/chat') ? 'bg-indigo-600 text-white shadow-md' : 'text-indigo-100 hover:bg-[#312e81] hover:text-white'}`}
              >
                <div className="relative">
                  <MessageCircle size={18} className={isActive('/lecturer/chat') ? 'text-white' : 'text-indigo-300 group-hover:text-white'} />
                  {unreadCount > 0 && (
                    <span className="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-sm border border-[#1e1b4b]">
                      {unreadCount}
                    </span>
                  )}
                </div>
                <span className="font-medium text-sm">Chat</span>
              </Link>

              <div className="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-2 px-3 mt-6">Sumber Belajar</div>
              <button 
                onClick={() => { openReferenceModal(); closeSidebar(); }}
                className="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 text-indigo-100 hover:bg-[#312e81] hover:text-white group"
              >
                <Book size={18} className="text-indigo-300 group-hover:text-white" />
                <span className="font-medium text-sm">Referensi Eksternal</span>
              </button>
              <button 
                onClick={() => { openRAGDocumentModal(); closeSidebar(); }}
                className="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 text-indigo-100 hover:bg-[#312e81] hover:text-white group"
              >
                <Database size={18} className="text-indigo-300 group-hover:text-white" />
                <span className="font-medium text-sm">Input Dokumen RAG</span>
              </button>
              <button 
                onClick={() => { openRAGChatbot(); closeSidebar(); }}
                className="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 text-indigo-100 hover:bg-[#312e81] hover:text-white group"
              >
                <Bot size={18} className="text-indigo-300 group-hover:text-white" />
                <span className="font-medium text-sm">Tanya AI</span>
              </button>
            </>
          )}

          {user.role === 'mahasiswa' && (
            <>
              <div className="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-2 px-3 mt-6">Pembelajaran</div>
              <Link 
                to="/student/classes" 
                onClick={closeSidebar}
                className={`flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 group ${isActive('/student/classes') ? 'bg-indigo-600 text-white shadow-md' : 'text-indigo-100 hover:bg-[#312e81] hover:text-white'}`}
              >
                <BookOpen size={18} className={isActive('/student/classes') ? 'text-white' : 'text-indigo-300 group-hover:text-white'} />
                <span className="font-medium text-sm">Kelas & Materi</span>
              </Link>
              <Link 
                to="/student/chat" 
                onClick={closeSidebar}
                className={`flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 group ${isActive('/student/chat') ? 'bg-indigo-600 text-white shadow-md' : 'text-indigo-100 hover:bg-[#312e81] hover:text-white'}`}
              >
                <div className="relative">
                  <MessageCircle size={18} className={isActive('/student/chat') ? 'text-white' : 'text-indigo-300 group-hover:text-white'} />
                  {unreadCount > 0 && (
                    <span className="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-sm border border-[#1e1b4b]">
                      {unreadCount}
                    </span>
                  )}
                </div>
                <span className="font-medium text-sm">Diskusi</span>
              </Link>

              <div className="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-2 px-3 mt-6">Sumber Belajar</div>
              <button 
                onClick={() => { openReferenceModal(); closeSidebar(); }}
                className="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 text-indigo-100 hover:bg-[#312e81] hover:text-white group"
              >
                <Book size={18} className="text-indigo-300 group-hover:text-white" />
                <span className="font-medium text-sm">Referensi</span>
              </button>
              <button 
                onClick={() => { openRAGChatbot(); closeSidebar(); }}
                className="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all duration-200 text-indigo-100 hover:bg-[#312e81] hover:text-white group"
              >
                <Bot size={18} className="text-indigo-300 group-hover:text-white" />
                <span className="font-medium text-sm">Tanya AI</span>
              </button>
            </>
          )}
        </nav>
        
        <div className="p-4 border-t border-[#312e81] bg-[#17153b]">
          <div className="flex items-center gap-3 mb-3">
            <div className="w-9 h-9 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold shadow-md">
              {user.name.charAt(0)}
            </div>
            <div className="overflow-hidden">
              <div className="font-bold text-sm truncate">{user.name}</div>
              <div className="text-xs text-indigo-300 capitalize">{user.role}</div>
            </div>
          </div>
          <button 
            onClick={logout}
            className="w-full flex items-center justify-center space-x-2 p-2 rounded-lg bg-[#312e81] hover:bg-red-600 text-indigo-200 hover:text-white transition-all duration-200 text-sm font-medium"
          >
            <LogOut size={16} />
            <span>Keluar</span>
          </button>
        </div>
      </aside>

      {/* Main Content */}
      <main className="flex-1 p-4 md:p-8 overflow-y-auto h-screen pt-20 md:pt-8 transition-all duration-300">
        <div className="max-w-7xl mx-auto">
          <Outlet />
        </div>
      </main>
    </div>
  );
}
