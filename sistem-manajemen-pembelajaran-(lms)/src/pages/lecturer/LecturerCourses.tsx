import React, { useEffect, useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import { Book, ChevronRight } from 'lucide-react';
import { Link } from 'react-router-dom';

interface Course {
  id: number;
  name: string;
  code: string;
  description: string;
  classes: any[];
}

export default function LecturerCourses() {
  const { user } = useAuth();
  const [courses, setCourses] = useState<Course[]>([]);

  useEffect(() => {
    if (user) fetchCourses();
  }, [user]);

  const fetchCourses = async () => {
    const res = await fetch(`/api/lecturer/${user?.id}/courses`);
    if (res.ok) {
      setCourses(await res.json());
    }
  };

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-800 mb-6">Mata Kuliah Saya</h1>
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {courses.map(course => (
          <Link 
            key={course.id} 
            to={`/lecturer/courses/${course.id}`}
            className="bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition group"
          >
            <div className="flex justify-between items-start mb-4">
              <div className="bg-indigo-100 p-3 rounded-lg text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition">
                <Book size={24} />
              </div>
              <span className="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded font-mono font-bold">{course.code}</span>
            </div>
            <h3 className="text-xl font-bold text-gray-800 mb-2">{course.name}</h3>
            <p className="text-gray-600 text-sm mb-4 line-clamp-2">{course.description}</p>
            <div className="flex items-center text-indigo-600 text-sm font-medium">
              Lihat Daftar Kelas <ChevronRight size={16} className="ml-1" />
            </div>
          </Link>
        ))}
      </div>
    </div>
  );
}
