import React, { useEffect, useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import { Plus, Book, Trash2, Edit, Layers, Calendar, Bell, ExternalLink, Bot, Database } from 'lucide-react';
import { motion } from 'motion/react';
import { useReference } from '../../context/ReferenceContext';

interface Class {
  id: number;
  name: string;
  code: string;
}

interface Course {
  id: number;
  name: string;
  code: string;
  description: string;
  classes: Class[];
}

interface Announcement {
  id: number;
  title: string;
  content: string;
  created_at: string;
  author_name: string;
}

export default function LecturerDashboard() {
  const { user } = useAuth();
  const { openReferenceModal, openRAGChatbot, openRAGDocumentModal } = useReference();
  const [activeTab, setActiveTab] = useState<'courses' | 'schedule' | 'announcements'>('courses');
  const [courses, setCourses] = useState<Course[]>([]);
  const [announcements, setAnnouncements] = useState<Announcement[]>([]);
  
  // Modals state
  const [showCourseModal, setShowCourseModal] = useState(false);
  const [showClassModal, setShowClassModal] = useState(false);
  const [showAnnouncementModal, setShowAnnouncementModal] = useState(false);
  const [selectedCourse, setSelectedCourse] = useState<Course | null>(null);
  
  // Forms state
  const [courseForm, setCourseForm] = useState({ name: '', code: '', description: '' });
  const [classForm, setClassForm] = useState({ name: '', code: '' });
  const [announcementForm, setAnnouncementForm] = useState({ title: '', content: '' });

  useEffect(() => {
    if (user) {
      fetchCourses();
      fetchAnnouncements();
    }
  }, [user]);

  const fetchCourses = async () => {
    const res = await fetch(`/api/lecturer/${user?.id}/courses`);
    if (res.ok) {
      setCourses(await res.json());
    }
  };

  const fetchAnnouncements = async () => {
    const res = await fetch('/api/announcements');
    if (res.ok) {
      setAnnouncements(await res.json());
    }
  };

  const handleCreateAnnouncement = async (e: React.FormEvent) => {
    e.preventDefault();
    const res = await fetch('/api/announcements', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ...announcementForm, author_id: user?.id }),
    });
    if (res.ok) {
      setShowAnnouncementModal(false);
      setAnnouncementForm({ title: '', content: '' });
      fetchAnnouncements();
    } else {
      alert('Gagal membuat pengumuman.');
    }
  };

  const handleDeleteAnnouncement = async (id: number) => {
    if (confirm('Hapus pengumuman ini?')) {
      await fetch(`/api/announcements/${id}`, { method: 'DELETE' });
      fetchAnnouncements();
    }
  };

  const handleCreateCourse = async (e: React.FormEvent) => {
    e.preventDefault();
    const res = await fetch('/api/courses', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ...courseForm, lecturer_id: user?.id }),
    });
    if (res.ok) {
      setShowCourseModal(false);
      setCourseForm({ name: '', code: '', description: '' });
      fetchCourses();
    } else {
      alert('Gagal membuat mata kuliah. Kode mungkin duplikat.');
    }
  };

  const handleDeleteCourse = async (id: number) => {
    if (confirm('Hapus mata kuliah ini beserta semua kelas di dalamnya?')) {
      await fetch(`/api/courses/${id}`, { method: 'DELETE' });
      fetchCourses();
    }
  };

  const handleAddClass = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedCourse) return;
    
    const res = await fetch('/api/classes', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        ...classForm, 
        course_id: selectedCourse.id,
        lecturer_id: user?.id 
      }),
    });

    if (res.ok) {
      setShowClassModal(false);
      setClassForm({ name: '', code: '' });
      fetchCourses(); // Refresh to see new class
    } else {
      alert('Gagal membuat kelas. Kode mungkin duplikat.');
    }
  };

  const openClassModal = (course: Course) => {
    setSelectedCourse(course);
    setShowClassModal(true);
  };

  return (
    <div>
      {/* Tab Navigation */}
      <div className="flex flex-col md:flex-row md:items-center justify-between mb-8 border-b border-gray-200">
        <div className="flex space-x-4 pb-1">
          <button 
            onClick={() => setActiveTab('courses')}
            className={`flex items-center gap-2 px-4 py-2 font-medium transition-colors relative ${
              activeTab === 'courses' ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700'
            }`}
          >
            <Book size={18} /> Mata Kuliah
            {activeTab === 'courses' && <motion.div layoutId="tab-underline" className="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600" />}
          </button>
          <button 
            onClick={() => setActiveTab('schedule')}
            className={`flex items-center gap-2 px-4 py-2 font-medium transition-colors relative ${
              activeTab === 'schedule' ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700'
            }`}
          >
            <Calendar size={18} /> Jadwal
            {activeTab === 'schedule' && <motion.div layoutId="tab-underline" className="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600" />}
          </button>
          <button 
            onClick={() => setActiveTab('announcements')}
            className={`flex items-center gap-2 px-4 py-2 font-medium transition-colors relative ${
              activeTab === 'announcements' ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700'
            }`}
          >
            <Bell size={18} /> Pengumuman
            {activeTab === 'announcements' && <motion.div layoutId="tab-underline" className="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600" />}
          </button>
        </div>

        <div className="flex gap-2">
          <button 
            onClick={openReferenceModal}
            className="mb-2 md:mb-0 flex items-center gap-2 px-4 py-1.5 bg-white border border-indigo-100 rounded-lg text-indigo-600 font-bold text-sm hover:bg-indigo-50 transition-all shadow-sm active:scale-95"
          >
            <Book size={16} />
            Referensi
          </button>
          <button 
            onClick={openRAGDocumentModal}
            className="mb-2 md:mb-0 flex items-center gap-2 px-4 py-1.5 bg-white border border-indigo-100 rounded-lg text-indigo-600 font-bold text-sm hover:bg-indigo-50 transition-all shadow-sm active:scale-95"
          >
            <Database size={16} />
            Input Dokumen RAG
          </button>
          <button 
            onClick={openRAGChatbot}
            className="mb-2 md:mb-0 flex items-center gap-2 px-4 py-1.5 bg-indigo-600 text-white rounded-lg font-bold text-sm hover:bg-indigo-700 transition-all shadow-md shadow-indigo-100 active:scale-95"
          >
            <Bot size={16} />
            Tanya AI
          </button>
        </div>
      </div>

      {/* Tab Content */}
      {activeTab === 'courses' && (
        <div>
          {courses.length === 0 ? (
             <div className="flex flex-col items-center justify-center min-h-[50vh] text-center p-8">
               <div className="bg-indigo-50 p-6 rounded-full mb-6">
                 <Layers size={64} className="text-indigo-600" />
               </div>
               <h2 className="text-3xl font-bold text-gray-800 mb-2">Ruang Tunggu</h2>
               <p className="text-gray-600 mb-8 max-w-md">
                 Belum ada aktivitas pembelajaran. Mulai dengan membuat Mata Kuliah pertama Anda.
               </p>
               <div className="flex gap-4">
                 <button 
                   onClick={() => setShowCourseModal(true)}
                   className="bg-indigo-600 text-white px-6 py-3 rounded-xl shadow-lg hover:bg-indigo-700 transition flex items-center gap-2 font-medium"
                 >
                   <Plus size={20} /> Buat Mata Kuliah
                 </button>
                 <button 
                   onClick={() => setActiveTab('announcements')}
                   className="bg-white text-indigo-600 border border-indigo-200 px-6 py-3 rounded-xl shadow-sm hover:bg-indigo-50 transition flex items-center gap-2 font-medium"
                 >
                   <Bell size={20} /> Buat Pengumuman
                 </button>
               </div>
             </div>
          ) : (
            <>
              <div className="flex justify-between items-center mb-6">
                <h2 className="text-2xl font-bold text-gray-800">Daftar Mata Kuliah</h2>
                <button 
                  onClick={() => setShowCourseModal(true)}
                  className="bg-indigo-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-indigo-700 shadow-sm"
                >
                  <Plus size={20} /> Tambah Mata Kuliah
                </button>
              </div>

              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {courses.map(course => (
                  <div key={course.id} className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div className="p-6">
                      <div className="flex justify-between items-start mb-4">
                        <div>
                          <span className="bg-indigo-100 text-indigo-700 text-xs px-2 py-1 rounded font-mono font-bold">{course.code}</span>
                          <h3 className="text-xl font-bold text-gray-800 mt-2">{course.name}</h3>
                          <p className="text-gray-600 text-sm mt-1">{course.description}</p>
                        </div>
                        <div className="flex gap-2">
                          <button onClick={() => alert('Edit feature coming soon')} className="p-2 text-gray-400 hover:text-indigo-600 transition">
                            <Edit size={18} />
                          </button>
                          <button onClick={() => handleDeleteCourse(course.id)} className="p-2 text-gray-400 hover:text-red-600 transition">
                            <Trash2 size={18} />
                          </button>
                        </div>
                      </div>

                      <div className="border-t border-gray-100 pt-4">
                        <div className="flex justify-between items-center mb-3">
                          <h4 className="text-sm font-semibold text-gray-700">Daftar Kelas</h4>
                          <button 
                            onClick={() => openClassModal(course)}
                            className="text-xs bg-indigo-50 text-indigo-600 px-2 py-1 rounded hover:bg-indigo-100 font-medium"
                          >
                            + Tambah Kelas
                          </button>
                        </div>
                        
                        {course.classes.length === 0 ? (
                          <p className="text-xs text-gray-400 italic">Belum ada kelas.</p>
                        ) : (
                          <div className="flex flex-wrap gap-2">
                            {course.classes.map(cls => (
                              <span key={cls.id} className="bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full border border-gray-200">
                                {cls.name} ({cls.code})
                              </span>
                            ))}
                          </div>
                        )}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </>
          )}
        </div>
      )}

      {activeTab === 'schedule' && (
        <div className="text-center py-12 text-gray-500">
          <Calendar size={48} className="mx-auto mb-4 opacity-20" />
          <p>Fitur Jadwal akan segera hadir.</p>
        </div>
      )}

      {activeTab === 'announcements' && (
        <div>
          <div className="flex justify-between items-center mb-6">
            <h2 className="text-2xl font-bold text-gray-800">Pengumuman</h2>
            <button 
              onClick={() => setShowAnnouncementModal(true)}
              className="bg-indigo-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-indigo-700 shadow-sm"
            >
              <Plus size={20} /> Buat Pengumuman
            </button>
          </div>

          <div className="space-y-4">
            {announcements.length === 0 ? (
              <p className="text-gray-500 text-center py-8">Belum ada pengumuman.</p>
            ) : (
              announcements.map((ann) => (
                <div key={ann.id} className="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                  <div className="flex justify-between items-start">
                    <div>
                      <h3 className="text-xl font-bold text-gray-800">{ann.title}</h3>
                      <p className="text-xs text-gray-500 mt-1">
                        Oleh {ann.author_name} • {new Date(ann.created_at).toLocaleDateString()}
                      </p>
                      <p className="text-gray-600 mt-3 whitespace-pre-wrap">{ann.content}</p>
                    </div>
                    <button 
                      onClick={() => handleDeleteAnnouncement(ann.id)}
                      className="text-gray-400 hover:text-red-600 p-2"
                    >
                      <Trash2 size={18} />
                    </button>
                  </div>
                </div>
              ))
            )}
          </div>
        </div>
      )}

      {/* Course Modal */}
      {showCourseModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
          <div className="bg-white p-6 rounded-xl shadow-lg w-full max-w-lg">
            <h3 className="text-lg font-bold mb-4">Buat Mata Kuliah Baru</h3>
            <form onSubmit={handleCreateCourse} className="space-y-4">
              <input
                placeholder="Nama Mata Kuliah"
                value={courseForm.name}
                onChange={e => setCourseForm({...courseForm, name: e.target.value})}
                className="border p-2 rounded w-full"
                required
              />
              <input
                placeholder="Kode MK (Misal: IF101)"
                value={courseForm.code}
                onChange={e => setCourseForm({...courseForm, code: e.target.value})}
                className="border p-2 rounded w-full"
                required
              />
              <textarea
                placeholder="Deskripsi Singkat"
                value={courseForm.description}
                onChange={e => setCourseForm({...courseForm, description: e.target.value})}
                className="border p-2 rounded w-full"
              />
              <div className="flex justify-end gap-2">
                <button 
                  type="button" 
                  onClick={() => setShowCourseModal(false)}
                  className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded"
                >
                  Batal
                </button>
                <button type="submit" className="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                  Simpan
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Class Modal */}
      {showClassModal && selectedCourse && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
          <div className="bg-white p-6 rounded-xl shadow-lg w-full max-w-lg">
            <h3 className="text-lg font-bold mb-4">Tambah Kelas untuk {selectedCourse.name}</h3>
            <form onSubmit={handleAddClass} className="space-y-4">
              <input
                placeholder="Nama Kelas (Misal: Kelas A)"
                value={classForm.name}
                onChange={e => setClassForm({...classForm, name: e.target.value})}
                className="border p-2 rounded w-full"
                required
              />
              <input
                placeholder="Kode Unik Kelas (Untuk Join Mahasiswa)"
                value={classForm.code}
                onChange={e => setClassForm({...classForm, code: e.target.value})}
                className="border p-2 rounded w-full"
                required
              />
              <div className="flex justify-end gap-2">
                <button 
                  type="button" 
                  onClick={() => setShowClassModal(false)}
                  className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded"
                >
                  Batal
                </button>
                <button type="submit" className="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                  Simpan
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
      {/* Announcement Modal */}
      {showAnnouncementModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
          <div className="bg-white p-6 rounded-xl shadow-lg w-full max-w-lg">
            <h3 className="text-lg font-bold mb-4">Buat Pengumuman Baru</h3>
            <form onSubmit={handleCreateAnnouncement} className="space-y-4">
              <input
                placeholder="Judul Pengumuman"
                value={announcementForm.title}
                onChange={e => setAnnouncementForm({...announcementForm, title: e.target.value})}
                className="border p-2 rounded w-full"
                required
              />
              <textarea
                placeholder="Isi Pengumuman"
                value={announcementForm.content}
                onChange={e => setAnnouncementForm({...announcementForm, content: e.target.value})}
                className="border p-2 rounded w-full h-32"
                required
              />
              <div className="flex justify-end gap-2">
                <button 
                  type="button" 
                  onClick={() => setShowAnnouncementModal(false)}
                  className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded"
                >
                  Batal
                </button>
                <button type="submit" className="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                  Simpan
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
