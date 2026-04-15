import React, { useEffect, useState } from 'react';
import { useAuth } from '../context/AuthContext';
import LecturerDashboard from './lecturer/LecturerDashboard';
import { Coffee, Bell, Users, BookOpen, CheckCircle, Clock, BarChart2, List } from 'lucide-react';
import { Link } from 'react-router-dom';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Cell } from 'recharts';

interface Announcement {
  id: number;
  title: string;
  content: string;
  created_at: string;
  author_name: string;
}

interface Student {
  id: number;
  name: string;
  username: string;
}

interface DashboardStats {
  total: number;
  completed: number;
  inProgress: number;
  notStarted: number;
  averageGrade: number;
}

interface AssignmentData {
  id: number;
  title: string;
  type: string;
  due_date: string;
  class_name: string;
  meeting_title: string;
  status: string;
  grade: number | null;
}

export default function Dashboard() {
  const { user } = useAuth();
  const [hasClasses, setHasClasses] = useState(false);
  const [loading, setLoading] = useState(true);
  const [announcements, setAnnouncements] = useState<Announcement[]>([]);
  const [students, setStudents] = useState<Student[]>([]);
  
  // Student Dashboard State
  const [dashboardData, setDashboardData] = useState<{ assignments: AssignmentData[], stats: DashboardStats } | null>(null);
  const [activeTab, setActiveTab] = useState<'overview' | 'assignments'>('overview');

  useEffect(() => {
    if (user?.role === 'mahasiswa') {
      checkStudentClasses();
      fetchAnnouncements();
      fetchStudents();
      fetchDashboardData();
    } else {
      setLoading(false);
    }
  }, [user]);

  const checkStudentClasses = async () => {
    const res = await fetch(`/api/student/${user?.id}/classes`);
    if (res.ok) {
      const data = await res.json();
      setHasClasses(data.length > 0);
    }
    setLoading(false);
  };

  const fetchAnnouncements = async () => {
    const res = await fetch('/api/announcements');
    if (res.ok) {
      setAnnouncements(await res.json());
    }
  };

  const fetchStudents = async () => {
    const res = await fetch('/api/students');
    if (res.ok) {
      setStudents(await res.json());
    }
  };

  const fetchDashboardData = async () => {
    const res = await fetch(`/api/student/${user?.id}/dashboard`);
    if (res.ok) {
      setDashboardData(await res.json());
    }
  };

  if (loading) return <div className="p-8">Loading...</div>;

  // Lecturer View
  if (user?.role === 'dosen') {
    return <LecturerDashboard />;
  }

  // Admin View
  if (user?.role === 'admin') {
    return (
      <div>
        <h1 className="text-3xl font-bold text-gray-800 mb-6">Admin Dashboard</h1>
        <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
          <p>Selamat datang di panel admin.</p>
        </div>
      </div>
    );
  }

  // Student View
  if (user?.role === 'mahasiswa') {
    if (!hasClasses) {
      // Student Waiting Room
      return (
        <div className="max-w-6xl mx-auto">
          <div className="flex flex-col items-center justify-center min-h-[40vh] text-center p-8 mb-8">
            <div className="bg-orange-50 p-6 rounded-full mb-6">
              <Coffee size={64} className="text-orange-500" />
            </div>
            <h2 className="text-3xl font-bold text-gray-800 mb-2">Ruang Tunggu</h2>
            <p className="text-gray-600 mb-8 max-w-md">
              Anda belum terdaftar di kelas manapun. Silakan gabung kelas menggunakan kode yang diberikan dosen.
            </p>
            <Link 
              to="/student/classes"
              className="bg-indigo-600 text-white px-6 py-3 rounded-xl shadow-lg hover:bg-indigo-700 transition font-medium"
            >
              Cari & Gabung Kelas
            </Link>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {/* Announcements Section */}
            <div className="lg:col-span-2">
              <div className="flex items-center gap-3 mb-6">
                <Bell className="text-indigo-600" />
                <h3 className="text-xl font-bold text-gray-800">Pengumuman Terbaru</h3>
              </div>
              <div className="space-y-4">
                {announcements.length === 0 ? (
                  <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-200 text-center text-gray-500">
                    Belum ada pengumuman.
                  </div>
                ) : (
                  announcements.map((ann) => (
                    <div key={ann.id} className="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                      <h4 className="text-lg font-bold text-gray-800">{ann.title}</h4>
                      <p className="text-xs text-gray-500 mt-1 mb-3">
                        {ann.author_name} • {new Date(ann.created_at).toLocaleDateString()}
                      </p>
                      <p className="text-gray-600 whitespace-pre-wrap">{ann.content}</p>
                    </div>
                  ))
                )}
              </div>
            </div>

            {/* Students List Section */}
            <div>
              <div className="flex items-center gap-3 mb-6">
                <Users className="text-indigo-600" />
                <h3 className="text-xl font-bold text-gray-800">Mahasiswa Terdaftar</h3>
              </div>
              <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div className="p-4 bg-gray-50 border-b border-gray-200">
                  <span className="text-sm font-medium text-gray-500">Total: {students.length} Mahasiswa</span>
                </div>
                <div className="max-h-[400px] overflow-y-auto">
                  {students.map((student) => (
                    <div key={student.id} className="p-4 border-b border-gray-100 last:border-0 flex items-center gap-3 hover:bg-gray-50 transition">
                      <div className="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-xs">
                        {student.name.charAt(0).toUpperCase()}
                      </div>
                      <div>
                        <p className="text-sm font-medium text-gray-800">{student.name}</p>
                        <p className="text-xs text-gray-500">@{student.username}</p>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </div>
      );
    }

    // Normal Student Dashboard
    return (
      <div className="max-w-7xl mx-auto pb-20">
        <div className="flex justify-between items-center mb-8">
          <div>
            <h1 className="text-3xl font-bold text-gray-800">Dashboard</h1>
            <p className="text-gray-500 mt-1">Selamat datang kembali, {user?.name}!</p>
          </div>
        </div>

        {/* Tabs */}
        <div className="flex border-b border-gray-200 mb-8">
          <button
            onClick={() => setActiveTab('overview')}
            className={`px-6 py-3 font-medium text-sm flex items-center gap-2 border-b-2 transition ${activeTab === 'overview' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'}`}
          >
            <BarChart2 size={18} /> Overview
          </button>
          <button
            onClick={() => setActiveTab('assignments')}
            className={`px-6 py-3 font-medium text-sm flex items-center gap-2 border-b-2 transition ${activeTab === 'assignments' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'}`}
          >
            <List size={18} /> Daftar Tugas
          </button>
        </div>

        {activeTab === 'overview' && dashboardData && (
          <div className="space-y-8 animate-in fade-in duration-300">
            {/* Stats Cards */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
              <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                <div className="flex items-center gap-4">
                  <div className="p-3 bg-blue-50 text-blue-600 rounded-xl">
                    <BookOpen size={24} />
                  </div>
                  <div>
                    <p className="text-sm text-gray-500 font-medium">Total Tugas</p>
                    <h3 className="text-2xl font-bold text-gray-800">{dashboardData.stats.total}</h3>
                  </div>
                </div>
              </div>
              <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                <div className="flex items-center gap-4">
                  <div className="p-3 bg-green-50 text-green-600 rounded-xl">
                    <CheckCircle size={24} />
                  </div>
                  <div>
                    <p className="text-sm text-gray-500 font-medium">Selesai</p>
                    <h3 className="text-2xl font-bold text-gray-800">{dashboardData.stats.completed}</h3>
                  </div>
                </div>
              </div>
              <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                <div className="flex items-center gap-4">
                  <div className="p-3 bg-orange-50 text-orange-600 rounded-xl">
                    <Clock size={24} />
                  </div>
                  <div>
                    <p className="text-sm text-gray-500 font-medium">Proses / Pending</p>
                    <h3 className="text-2xl font-bold text-gray-800">{dashboardData.stats.inProgress + dashboardData.stats.notStarted}</h3>
                  </div>
                </div>
              </div>
              <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                <div className="flex items-center gap-4">
                  <div className="p-3 bg-purple-50 text-purple-600 rounded-xl">
                    <BarChart2 size={24} />
                  </div>
                  <div>
                    <p className="text-sm text-gray-500 font-medium">Rata-rata Nilai</p>
                    <h3 className="text-2xl font-bold text-gray-800">{dashboardData.stats.averageGrade.toFixed(1)}</h3>
                  </div>
                </div>
              </div>
            </div>

            {/* Chart Section */}
            <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
              <h3 className="text-lg font-bold text-gray-800 mb-6">Progress Nilai Tugas</h3>
              <div className="h-80">
                <ResponsiveContainer width="100%" height="100%">
                  <BarChart data={dashboardData.assignments.filter(a => a.grade !== null).slice(0, 10).reverse()}>
                    <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f0f0f0" />
                    <XAxis dataKey="title" tick={{fontSize: 12}} interval={0} angle={-45} textAnchor="end" height={80} />
                    <YAxis domain={[0, 100]} />
                    <Tooltip 
                      contentStyle={{borderRadius: '8px', border: 'none', boxShadow: '0 4px 12px rgba(0,0,0,0.1)'}}
                      cursor={{fill: '#f8fafc'}}
                    />
                    <Bar dataKey="grade" fill="#4f46e5" radius={[4, 4, 0, 0]} barSize={40}>
                      {dashboardData.assignments.map((entry, index) => (
                        <Cell key={`cell-${index}`} fill={entry.grade && entry.grade >= 75 ? '#4f46e5' : '#f59e0b'} />
                      ))}
                    </Bar>
                  </BarChart>
                </ResponsiveContainer>
              </div>
            </div>
          </div>
        )}

        {activeTab === 'assignments' && dashboardData && (
          <div className="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden animate-in fade-in duration-300">
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead className="bg-gray-50 border-b border-gray-200">
                  <tr>
                    <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
                    <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Judul Tugas</th>
                    <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mata Kuliah</th>
                    <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pertemuan</th>
                    <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tenggat</th>
                    <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nilai</th>
                    <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {dashboardData.assignments.length === 0 ? (
                    <tr>
                      <td colSpan={8} className="px-6 py-8 text-center text-gray-500">Belum ada tugas.</td>
                    </tr>
                  ) : (
                    dashboardData.assignments.map((assignment, index) => (
                      <tr key={assignment.id} className="hover:bg-gray-50 transition">
                        <td className="px-6 py-4 text-sm text-gray-500">{index + 1}</td>
                        <td className="px-6 py-4">
                          <div className="text-sm font-medium text-gray-900">{assignment.title}</div>
                          <div className="text-xs text-gray-500 uppercase">{assignment.type}</div>
                        </td>
                        <td className="px-6 py-4 text-sm text-gray-600">{assignment.class_name}</td>
                        <td className="px-6 py-4 text-sm text-gray-600">{assignment.meeting_title || '-'}</td>
                        <td className="px-6 py-4 text-sm text-gray-600">
                          {assignment.due_date ? new Date(assignment.due_date).toLocaleDateString('id-ID') : '-'}
                        </td>
                        <td className="px-6 py-4">
                          <span className={`px-2 py-1 text-xs font-bold rounded-full ${
                            assignment.status === 'Selesai' ? 'bg-green-100 text-green-700' :
                            assignment.status === 'Sedang Dikerjakan' ? 'bg-orange-100 text-orange-700' :
                            'bg-gray-100 text-gray-700'
                          }`}>
                            {assignment.status}
                          </span>
                        </td>
                        <td className="px-6 py-4">
                          {assignment.grade !== null ? (
                            <span className={`font-bold ${assignment.grade >= 75 ? 'text-green-600' : 'text-orange-600'}`}>
                              {assignment.grade}
                            </span>
                          ) : (
                            <span className="text-gray-400">-</span>
                          )}
                        </td>
                        <td className="px-6 py-4">
                          <Link 
                            to={`/student/assignments/${assignment.id}`}
                            className="text-indigo-600 hover:text-indigo-800 text-sm font-medium"
                          >
                            {assignment.status === 'Selesai' ? 'Lihat' : 'Kerjakan'}
                          </Link>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </div>
        )}
      </div>
    );
  }

  return <div>Role not recognized</div>;
}
