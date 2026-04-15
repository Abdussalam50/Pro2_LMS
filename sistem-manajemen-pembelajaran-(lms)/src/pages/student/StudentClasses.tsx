import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { Search, BookOpen, Book, Bot } from 'lucide-react';
import { useReference } from '../../context/ReferenceContext';

interface Class {
  id: number;
  name: string; // Class Name
  code: string;
  course_name: string;
  course_description: string;
  lecturer_name: string;
}

export default function StudentClasses() {
  const { user } = useAuth();
  const { openReferenceModal, openRAGChatbot } = useReference();
  const [classes, setClasses] = useState<Class[]>([]);
  const [joinCode, setJoinCode] = useState('');

  useEffect(() => {
    if (user) fetchClasses();
  }, [user]);

  const fetchClasses = async () => {
    const res = await fetch(`/api/student/${user?.id}/classes`);
    if (res.ok) {
      setClasses(await res.json());
    }
  };

  const handleJoin = async (e: React.FormEvent) => {
    e.preventDefault();
    const res = await fetch('/api/classes/join', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ class_code: joinCode, student_id: user?.id }),
    });
    
    if (res.ok) {
      setJoinCode('');
      fetchClasses();
      alert('Berhasil bergabung ke kelas!');
    } else {
      const data = await res.json();
      alert(data.error || 'Gagal bergabung');
    }
  };

  return (
    <div>
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div className="flex items-center gap-2">
          <h1 className="text-2xl font-bold">Kelas Saya</h1>
          <button 
            onClick={openReferenceModal}
            className="flex items-center gap-2 px-3 py-1.5 bg-white border border-indigo-100 rounded-lg text-indigo-600 font-bold text-xs hover:bg-indigo-50 transition-all shadow-sm active:scale-95"
          >
            <Book size={14} />
            Referensi
          </button>
          <button 
            onClick={openRAGChatbot}
            className="flex items-center gap-2 px-3 py-1.5 bg-indigo-600 text-white rounded-lg font-bold text-xs hover:bg-indigo-700 transition-all shadow-md shadow-indigo-100 active:scale-95"
          >
            <Bot size={14} />
            Tanya AI
          </button>
        </div>
        
        <form onSubmit={handleJoin} className="flex gap-2 w-full md:w-auto">
          <input
            placeholder="Masukkan Kode Kelas"
            value={joinCode}
            onChange={e => setJoinCode(e.target.value)}
            className="border p-2 rounded-lg w-full md:w-64 focus:ring-2 focus:ring-indigo-500 outline-none"
            required
          />
          <button type="submit" className="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 whitespace-nowrap">
            Gabung Kelas
          </button>
        </form>
      </div>

      {classes.length === 0 ? (
        <div className="text-center py-12 bg-white rounded-xl border border-dashed border-gray-300">
          <BookOpen className="mx-auto text-gray-400 mb-4" size={48} />
          <h3 className="text-lg font-medium text-gray-900">Belum ada kelas</h3>
          <p className="text-gray-500">Silakan gabung kelas menggunakan kode dari dosen.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {classes.map(cls => (
            <div key={cls.id} className="bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition cursor-pointer group">
              <div className="flex justify-between items-start mb-4">
                <div className="bg-green-100 p-3 rounded-lg text-green-600 group-hover:bg-green-200 transition">
                  <BookOpen size={24} />
                </div>
                <span className="text-xs font-medium text-gray-500">Dosen: {cls.lecturer_name}</span>
              </div>
              <h3 className="text-xl font-bold text-gray-800 mb-1">{cls.course_name}</h3>
              <p className="text-sm font-medium text-indigo-600 mb-2">Kelas: {cls.name}</p>
              <p className="text-gray-600 text-sm mb-4 line-clamp-2">{cls.course_description}</p>
              <Link to={`/student/classes/${cls.id}`} className="block w-full text-center bg-indigo-50 text-indigo-700 py-2 rounded hover:bg-indigo-100 text-sm font-medium">
                Lihat Kelas
              </Link>
              <div className="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden mt-4">
                <div className="bg-green-500 h-full w-3/4"></div> {/* Mock progress */}
              </div>
              <p className="text-xs text-gray-500 mt-2 text-right">75% Selesai</p>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
