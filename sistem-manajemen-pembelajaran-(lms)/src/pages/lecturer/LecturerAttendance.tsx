import React, { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import { BookOpen, Users, Calendar, CheckCircle, XCircle, Plus, Eye, Lock, Unlock, ArrowRight } from 'lucide-react';

interface Course {
  id: number;
  name: string;
  code: string;
  classes: Class[];
}

interface Class {
  id: number;
  name: string;
  code: string;
}

interface Meeting {
  id: number;
  title: string;
  date: string;
}

interface AttendanceSession {
  id: number;
  class_id: number;
  meeting_id: number;
  meeting_title: string;
  code: string;
  status: 'open' | 'closed';
  created_at: string;
}

interface AttendanceSubmission {
  id: number;
  student_name: string;
  student_id_number: string;
  submitted_at: string;
}

export default function LecturerAttendance() {
  const { user } = useAuth();
  const [courses, setCourses] = useState<Course[]>([]);
  const [selectedCourse, setSelectedCourse] = useState<Course | null>(null);
  const [selectedClass, setSelectedClass] = useState<Class | null>(null);
  const [meetings, setMeetings] = useState<Meeting[]>([]);
  const [selectedMeetingId, setSelectedMeetingId] = useState<number | ''>('');
  const [attendanceCode, setAttendanceCode] = useState('');
  const [attendanceSessions, setAttendanceSessions] = useState<AttendanceSession[]>([]);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);

  // Report Modal
  const [showReport, setShowReport] = useState(false);
  const [selectedSession, setSelectedSession] = useState<AttendanceSession | null>(null);
  const [submissions, setSubmissions] = useState<AttendanceSubmission[]>([]);

  useEffect(() => {
    if (user) {
      fetchCourses();
    }
  }, [user]);

  const fetchCourses = async () => {
    try {
      const res = await fetch(`/api/lecturer/${user?.id}/courses`);
      if (res.ok) {
        setCourses(await res.json());
      }
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  const handleCourseSelect = (course: Course) => {
    setSelectedCourse(course);
    setSelectedClass(null);
    setAttendanceSessions([]);
    setMeetings([]);
  };

  const handleClassSelect = async (cls: Class) => {
    setSelectedClass(cls);
    fetchMeetings(cls.id);
    fetchAttendanceSessions(cls.id);
  };

  const fetchMeetings = async (classId: number) => {
    try {
      const res = await fetch(`/api/classes/${classId}/meetings`);
      if (res.ok) {
        setMeetings(await res.json());
      }
    } catch (e) {
      console.error(e);
    }
  };

  const fetchAttendanceSessions = async (classId: number) => {
    try {
      const res = await fetch(`/api/classes/${classId}/attendance`);
      if (res.ok) {
        setAttendanceSessions(await res.json());
      }
    } catch (e) {
      console.error(e);
    }
  };

  const generateCode = () => {
    const code = Math.random().toString(36).substring(2, 8).toUpperCase();
    setAttendanceCode(code);
  };

  const handleCreateAttendance = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedClass || !selectedMeetingId || !attendanceCode) return;

    setSubmitting(true);
    try {
      const res = await fetch('/api/attendance', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          class_id: selectedClass.id,
          meeting_id: selectedMeetingId,
          code: attendanceCode,
          lecturer_id: user?.id
        })
      });

      if (res.ok) {
        alert('Sesi presensi berhasil dibuat dan diposting ke chat!');
        setAttendanceCode('');
        setSelectedMeetingId('');
        fetchAttendanceSessions(selectedClass.id);
      } else {
        alert('Gagal membuat sesi presensi');
      }
    } catch (e) {
      console.error(e);
    } finally {
      setSubmitting(false);
    }
  };

  const handleCloseAttendance = async (sessionId: number) => {
    if (!confirm('Tutup sesi presensi ini? Mahasiswa tidak akan bisa melakukan presensi lagi.')) return;

    try {
      const res = await fetch(`/api/attendance/${sessionId}/close`, { method: 'POST' });
      if (res.ok && selectedClass) {
        fetchAttendanceSessions(selectedClass.id);
      }
    } catch (e) {
      console.error(e);
    }
  };

  const handleViewReport = async (session: AttendanceSession) => {
    setSelectedSession(session);
    try {
      const res = await fetch(`/api/attendance/${session.id}/submissions`);
      if (res.ok) {
        setSubmissions(await res.json());
        setShowReport(true);
      }
    } catch (e) {
      console.error(e);
    }
  };

  if (loading) return <div className="p-8 text-center">Loading...</div>;

  return (
    <div className="max-w-6xl mx-auto">
      <div className="flex items-center gap-3 mb-8">
        <div className="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-200">
          <Calendar className="text-white" size={24} />
        </div>
        <div>
          <h1 className="text-3xl font-bold text-gray-900 tracking-tight">Manajemen Presensi</h1>
          <p className="text-gray-500 font-medium">Kelola kehadiran mahasiswa per pertemuan</p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Selection Panel */}
        <div className="lg:col-span-1 space-y-6">
          <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h2 className="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
              <BookOpen size={18} className="text-indigo-600" /> Pilih Mata Kuliah
            </h2>
            <div className="space-y-2">
              {courses.map(course => (
                <button
                  key={course.id}
                  onClick={() => handleCourseSelect(course)}
                  className={`w-full text-left p-4 rounded-xl transition-all duration-200 border ${selectedCourse?.id === course.id ? 'bg-indigo-50 border-indigo-200 text-indigo-700 shadow-sm' : 'bg-white border-gray-100 hover:border-indigo-200 hover:bg-gray-50 text-gray-700'}`}
                >
                  <div className="font-bold text-sm">{course.name}</div>
                  <div className="text-xs opacity-70">{course.code}</div>
                </button>
              ))}
            </div>
          </div>

          {selectedCourse && (
            <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 animate-in fade-in slide-in-from-top-4 duration-300">
              <h2 className="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <Users size={18} className="text-indigo-600" /> Pilih Kelas
              </h2>
              <div className="space-y-2">
                {selectedCourse.classes.map(cls => (
                  <button
                    key={cls.id}
                    onClick={() => handleClassSelect(cls)}
                    className={`w-full text-left p-4 rounded-xl transition-all duration-200 border ${selectedClass?.id === cls.id ? 'bg-indigo-50 border-indigo-200 text-indigo-700 shadow-sm' : 'bg-white border-gray-100 hover:border-indigo-200 hover:bg-gray-50 text-gray-700'}`}
                  >
                    <div className="font-bold text-sm">{cls.name}</div>
                    <div className="text-xs opacity-70">{cls.code}</div>
                  </button>
                ))}
              </div>
            </div>
          )}
        </div>

        {/* Main Content Panel */}
        <div className="lg:col-span-2 space-y-6">
          {!selectedClass ? (
            <div className="bg-white rounded-2xl border-2 border-dashed border-gray-200 p-12 text-center">
              <div className="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <ArrowRight className="text-gray-300" size={32} />
              </div>
              <h3 className="text-lg font-bold text-gray-400">Pilih mata kuliah dan kelas untuk mengelola presensi</h3>
            </div>
          ) : (
            <>
              {/* Create Attendance Form */}
              <div className="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <h2 className="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                  <Plus size={20} className="text-indigo-600" /> Buat Sesi Presensi Baru
                </h2>
                <form onSubmit={handleCreateAttendance} className="space-y-6">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label className="block text-sm font-bold text-gray-700 mb-2">Pilih Pertemuan</label>
                      <select
                        value={selectedMeetingId}
                        onChange={e => setSelectedMeetingId(e.target.value ? Number(e.target.value) : '')}
                        className="w-full p-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none bg-gray-50"
                        required
                      >
                        <option value="">-- Pilih Pertemuan --</option>
                        {meetings.map(m => (
                          <option key={m.id} value={m.id}>{m.title} ({new Date(m.date).toLocaleDateString()})</option>
                        ))}
                      </select>
                    </div>
                    <div>
                      <label className="block text-sm font-bold text-gray-700 mb-2">Kode Presensi</label>
                      <div className="flex gap-2">
                        <input
                          type="text"
                          value={attendanceCode}
                          onChange={e => setAttendanceCode(e.target.value.toUpperCase())}
                          className="flex-1 p-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none bg-gray-50 font-mono font-bold text-center tracking-widest"
                          placeholder="KODE12"
                          required
                        />
                        <button
                          type="button"
                          onClick={generateCode}
                          className="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 transition-colors font-bold text-xs"
                        >
                          Acak
                        </button>
                      </div>
                    </div>
                  </div>
                  <button
                    type="submit"
                    disabled={submitting}
                    className="w-full bg-indigo-600 text-white p-4 rounded-xl font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-[0.98] disabled:opacity-50 flex items-center justify-center gap-2"
                  >
                    {submitting ? 'Memproses...' : <><CheckCircle size={20} /> Simpan & Posting ke Chat</>}
                  </button>
                </form>
              </div>

              {/* Attendance History */}
              <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div className="p-6 border-b border-gray-50 bg-gray-50/50">
                  <h2 className="font-bold text-gray-800">Riwayat Sesi Presensi</h2>
                </div>
                <div className="overflow-x-auto">
                  <table className="w-full text-left">
                    <thead>
                      <tr className="text-xs font-bold text-gray-400 uppercase tracking-wider">
                        <th className="px-6 py-4">Pertemuan</th>
                        <th className="px-6 py-4">Kode</th>
                        <th className="px-6 py-4">Status</th>
                        <th className="px-6 py-4">Dibuat Pada</th>
                        <th className="px-6 py-4 text-right">Aksi</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-50">
                      {attendanceSessions.length === 0 ? (
                        <tr>
                          <td colSpan={5} className="px-6 py-12 text-center text-gray-400 italic">Belum ada sesi presensi untuk kelas ini</td>
                        </tr>
                      ) : (
                        attendanceSessions.map(session => (
                          <tr key={session.id} className="hover:bg-gray-50/50 transition-colors">
                            <td className="px-6 py-4">
                              <div className="font-bold text-gray-800 text-sm">{session.meeting_title}</div>
                            </td>
                            <td className="px-6 py-4">
                              <span className="font-mono font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded text-sm">{session.code}</span>
                            </td>
                            <td className="px-6 py-4">
                              {session.status === 'open' ? (
                                <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                  <Unlock size={12} /> Terbuka
                                </span>
                              ) : (
                                <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                                  <Lock size={12} /> Tertutup
                                </span>
                              )}
                            </td>
                            <td className="px-6 py-4 text-xs text-gray-500">
                              {new Date(session.created_at).toLocaleString()}
                            </td>
                            <td className="px-6 py-4 text-right">
                              <div className="flex justify-end gap-2">
                                <button
                                  onClick={() => handleViewReport(session)}
                                  className="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                  title="Lihat Laporan"
                                >
                                  <Eye size={18} />
                                </button>
                                {session.status === 'open' && (
                                  <button
                                    onClick={() => handleCloseAttendance(session.id)}
                                    className="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Tutup Sesi"
                                  >
                                    <Lock size={18} />
                                  </button>
                                )}
                              </div>
                            </td>
                          </tr>
                        ))
                      )}
                    </tbody>
                  </table>
                </div>
              </div>
            </>
          )}
        </div>
      </div>

      {/* Report Modal */}
      {showReport && selectedSession && (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 animate-in fade-in duration-200">
          <div className="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[80vh] flex flex-col overflow-hidden animate-in zoom-in-95 duration-200">
            <div className="p-8 border-b border-gray-100 flex justify-between items-center bg-indigo-600 text-white">
              <div>
                <h3 className="text-xl font-bold">Laporan Kehadiran</h3>
                <p className="text-indigo-100 text-sm font-medium">{selectedSession.meeting_title} - Kode: {selectedSession.code}</p>
              </div>
              <button onClick={() => setShowReport(false)} className="p-2 hover:bg-white/10 rounded-xl transition-colors">
                <XCircle size={28} />
              </button>
            </div>
            
            <div className="flex-1 overflow-y-auto p-8">
              <div className="mb-6 flex justify-between items-center bg-gray-50 p-4 rounded-2xl">
                <span className="text-sm font-bold text-gray-500">Total Hadir:</span>
                <span className="text-2xl font-black text-indigo-600">{submissions.length}</span>
              </div>

              <div className="space-y-3">
                {submissions.length === 0 ? (
                  <div className="text-center py-12 text-gray-400 italic">Belum ada mahasiswa yang melakukan presensi</div>
                ) : (
                  submissions.map((sub, idx) => (
                    <div key={sub.id} className="flex items-center justify-between p-4 rounded-xl border border-gray-100 hover:border-indigo-100 transition-colors">
                      <div className="flex items-center gap-4">
                        <div className="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center font-bold text-gray-400 text-sm">
                          {idx + 1}
                        </div>
                        <div>
                          <div className="font-bold text-gray-800">{sub.student_name}</div>
                          <div className="text-xs text-gray-500">{sub.student_id_number}</div>
                        </div>
                      </div>
                      <div className="text-right">
                        <div className="text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded">HADIR</div>
                        <div className="text-[10px] text-gray-400 mt-1">{new Date(sub.submitted_at).toLocaleTimeString()}</div>
                      </div>
                    </div>
                  ))
                )}
              </div>
            </div>
            
            <div className="p-6 border-t border-gray-100 bg-gray-50 text-center">
              <button
                onClick={() => setShowReport(false)}
                className="px-8 py-3 bg-white border border-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-100 transition-colors shadow-sm"
              >
                Tutup
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
