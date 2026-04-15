import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { ArrowLeft, Users, ChevronRight } from 'lucide-react';

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

export default function LecturerCourseDetail() {
  const { courseId } = useParams();
  const [course, setCourse] = useState<Course | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchCourseDetails();
  }, [courseId]);

  const fetchCourseDetails = async () => {
    // Reusing the lecturer courses endpoint and filtering client-side for simplicity, 
    // or we could add a specific endpoint. Since we have the list endpoint, let's use that for now 
    // or fetch all and find. Better to have a specific endpoint but for speed:
    // Actually, let's just fetch the courses list and find the one.
    // In a real app, GET /api/courses/:id would be better.
    
    // Let's assume we can fetch the specific course or filter from the list.
    // Since I didn't make a specific GET /courses/:id endpoint that returns classes, 
    // I'll fetch the list and filter.
    // Wait, I can use the existing /api/lecturer/:id/courses endpoint if I have the user ID, 
    // but here I only have courseId. 
    // Let's add a simple endpoint to get course details including classes.
    
    const res = await fetch(`/api/courses/${courseId}/details`); 
    if (res.ok) {
      setCourse(await res.json());
    }
    setLoading(false);
  };

  if (loading) return <div className="p-8">Loading...</div>;
  if (!course) return <div className="p-8">Mata Kuliah tidak ditemukan</div>;

  return (
    <div>
      <Link to="/lecturer/courses" className="flex items-center text-gray-600 hover:text-indigo-600 mb-6">
        <ArrowLeft size={20} className="mr-2" /> Kembali ke Daftar Mata Kuliah
      </Link>

      <div className="bg-white p-8 rounded-xl shadow-sm border border-gray-200 mb-8">
        <h1 className="text-3xl font-bold text-gray-800 mb-2">{course.name}</h1>
        <p className="text-gray-500 font-mono mb-4">{course.code}</p>
        <p className="text-gray-700">{course.description}</p>
      </div>

      <h2 className="text-2xl font-bold text-gray-800 mb-6">Daftar Kelas</h2>
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {course.classes.length === 0 ? (
          <p className="text-gray-500">Belum ada kelas di mata kuliah ini.</p>
        ) : (
          course.classes.map(cls => (
            <Link 
              key={cls.id} 
              to={`/lecturer/classes/${cls.id}`}
              className="bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition group"
            >
              <div className="flex justify-between items-start mb-4">
                <div className="bg-green-100 p-3 rounded-lg text-green-600 group-hover:bg-green-600 group-hover:text-white transition">
                  <Users size={24} />
                </div>
                <span className="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded font-mono font-bold">{cls.code}</span>
              </div>
              <h3 className="text-xl font-bold text-gray-800 mb-2">{cls.name}</h3>
              <div className="flex items-center text-indigo-600 text-sm font-medium">
                Masuk Kelas <ChevronRight size={16} className="ml-1" />
              </div>
            </Link>
          ))
        )}
      </div>
    </div>
  );
}
