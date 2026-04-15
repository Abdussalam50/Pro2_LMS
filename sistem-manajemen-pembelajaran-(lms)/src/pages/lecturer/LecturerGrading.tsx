import React, { useEffect, useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import { Save, ChevronRight, Users, Settings, Award } from 'lucide-react';

interface Class {
  id: number;
  name: string;
  code: string;
  course_name: string;
}

interface StudentGrade {
  id: number;
  name: string;
  username: string;
  grades: {
    latihan: number;
    tugas: number;
    ujian: number;
  };
  finalGrade: number;
}

export default function LecturerGrading() {
  const { user } = useAuth();
  const [classes, setClasses] = useState<Class[]>([]);
  const [selectedClassId, setSelectedClassId] = useState<number | null>(null);
  const [activeTab, setActiveTab] = useState<'recap' | 'settings'>('recap');
  
  // Data
  const [recapData, setRecapData] = useState<StudentGrade[]>([]);
  const [weights, setWeights] = useState({ latihan: 0, tugas: 0, ujian: 0 });
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (user) fetchClasses();
  }, [user]);

  useEffect(() => {
    if (selectedClassId) {
      fetchGradeData();
      fetchSettings();
    }
  }, [selectedClassId]);

  const fetchClasses = async () => {
    // We need a route to get all classes for lecturer, ideally with course name
    // Reuse existing route or fetch courses and flatten
    const res = await fetch(`/api/lecturer/${user?.id}/courses`);
    if (res.ok) {
      const courses = await res.json();
      const allClasses: Class[] = [];
      courses.forEach((c: any) => {
        c.classes.forEach((cls: any) => {
          allClasses.push({ ...cls, course_name: c.name });
        });
      });
      setClasses(allClasses);
    }
  };

  const fetchGradeData = async () => {
    setLoading(true);
    const res = await fetch(`/api/classes/${selectedClassId}/grades`);
    if (res.ok) {
      const data = await res.json();
      setRecapData(data.recap);
      
      // Also update weights from response if available, or fetch separately
      const weightMap = data.weights.reduce((acc: any, curr: any) => ({ ...acc, [curr.type]: curr.weight }), {});
      setWeights({
        latihan: weightMap.latihan || 0,
        tugas: weightMap.tugas || 0,
        ujian: weightMap.ujian || 0
      });
    }
    setLoading(false);
  };

  const fetchSettings = async () => {
    // Already fetched in fetchGradeData for simplicity, but can be separate
  };

  const handleSaveSettings = async () => {
    const total = weights.latihan + weights.tugas + weights.ujian;
    if (total !== 100) {
      alert(`Total bobot harus 100%. Saat ini: ${total}%`);
      return;
    }

    const settings = [
      { type: 'latihan', weight: weights.latihan },
      { type: 'tugas', weight: weights.tugas },
      { type: 'ujian', weight: weights.ujian }
    ];

    const res = await fetch(`/api/classes/${selectedClassId}/grading-settings`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ settings })
    });

    if (res.ok) {
      alert('Pengaturan penilaian disimpan');
      fetchGradeData(); // Refresh grades
    } else {
      alert('Gagal menyimpan pengaturan');
    }
  };

  if (!selectedClassId) {
    return (
      <div>
        <h1 className="text-2xl font-bold text-gray-800 mb-6">Penilaian Mahasiswa</h1>
        <p className="text-gray-600 mb-6">Pilih kelas untuk mengelola penilaian.</p>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {classes.map(cls => (
            <button
              key={cls.id}
              onClick={() => setSelectedClassId(cls.id)}
              className="bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition text-left group"
            >
              <div className="flex justify-between items-start mb-4">
                <div className="bg-purple-100 p-3 rounded-lg text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition">
                  <Award size={24} />
                </div>
                <span className="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded font-mono font-bold">{cls.code}</span>
              </div>
              <h3 className="text-xl font-bold text-gray-800 mb-1">{cls.name}</h3>
              <p className="text-sm text-gray-500 mb-4">{cls.course_name}</p>
              <div className="flex items-center text-purple-600 text-sm font-medium">
                Buka Penilaian <ChevronRight size={16} className="ml-1" />
              </div>
            </button>
          ))}
        </div>
      </div>
    );
  }

  const selectedClass = classes.find(c => c.id === selectedClassId);

  return (
    <div>
      <button 
        onClick={() => setSelectedClassId(null)}
        className="text-gray-500 hover:text-indigo-600 mb-6 flex items-center text-sm"
      >
        &larr; Kembali ke Daftar Kelas
      </button>

      <div className="flex justify-between items-center mb-6">
        <div>
          <h1 className="text-2xl font-bold text-gray-800">{selectedClass?.name}</h1>
          <p className="text-gray-600">{selectedClass?.course_name}</p>
        </div>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div className="flex border-b border-gray-200">
          <button
            onClick={() => setActiveTab('recap')}
            className={`px-6 py-3 text-sm font-medium flex items-center gap-2 ${activeTab === 'recap' ? 'bg-indigo-50 text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-600 hover:bg-gray-50'}`}
          >
            <Users size={18} /> Rekap Nilai
          </button>
          <button
            onClick={() => setActiveTab('settings')}
            className={`px-6 py-3 text-sm font-medium flex items-center gap-2 ${activeTab === 'settings' ? 'bg-indigo-50 text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-600 hover:bg-gray-50'}`}
          >
            <Settings size={18} /> Setting Penilaian
          </button>
        </div>

        <div className="p-6">
          {activeTab === 'recap' && (
            <div>
              {loading ? (
                <p>Loading...</p>
              ) : (
                <div className="overflow-x-auto">
                  <table className="w-full text-left border-collapse">
                    <thead>
                      <tr className="bg-gray-50 text-gray-600 text-sm">
                        <th className="p-3 border-b font-semibold">Mahasiswa</th>
                        <th className="p-3 border-b font-semibold text-center">Latihan ({weights.latihan}%)</th>
                        <th className="p-3 border-b font-semibold text-center">Tugas ({weights.tugas}%)</th>
                        <th className="p-3 border-b font-semibold text-center">Ujian ({weights.ujian}%)</th>
                        <th className="p-3 border-b font-semibold text-center">Nilai Akhir</th>
                      </tr>
                    </thead>
                    <tbody>
                      {recapData.map(student => (
                        <tr key={student.id} className="border-b last:border-0 hover:bg-gray-50">
                          <td className="p-3">
                            <div className="font-medium text-gray-800">{student.name}</div>
                            <div className="text-xs text-gray-500">{student.username}</div>
                          </td>
                          <td className="p-3 text-center">{student.grades.latihan.toFixed(1)}</td>
                          <td className="p-3 text-center">{student.grades.tugas.toFixed(1)}</td>
                          <td className="p-3 text-center">{student.grades.ujian.toFixed(1)}</td>
                          <td className="p-3 text-center font-bold text-indigo-600">{student.finalGrade.toFixed(1)}</td>
                        </tr>
                      ))}
                      {recapData.length === 0 && (
                        <tr>
                          <td colSpan={5} className="p-8 text-center text-gray-500">Belum ada data mahasiswa.</td>
                        </tr>
                      )}
                    </tbody>
                  </table>
                </div>
              )}
            </div>
          )}

          {activeTab === 'settings' && (
            <div className="max-w-lg">
              <h3 className="text-lg font-bold text-gray-800 mb-4">Bobot Penilaian</h3>
              <p className="text-sm text-gray-600 mb-6">
                Tentukan persentase bobot untuk setiap komponen penilaian. Total harus 100%.
              </p>
              
              <div className="space-y-4 mb-6">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Latihan (%)</label>
                  <input 
                    type="number" 
                    value={weights.latihan}
                    onChange={e => setWeights({...weights, latihan: Number(e.target.value)})}
                    className="border p-2 rounded w-full"
                    min="0" max="100"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Tugas (%)</label>
                  <input 
                    type="number" 
                    value={weights.tugas}
                    onChange={e => setWeights({...weights, tugas: Number(e.target.value)})}
                    className="border p-2 rounded w-full"
                    min="0" max="100"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Ujian (%)</label>
                  <input 
                    type="number" 
                    value={weights.ujian}
                    onChange={e => setWeights({...weights, ujian: Number(e.target.value)})}
                    className="border p-2 rounded w-full"
                    min="0" max="100"
                  />
                </div>
              </div>

              <div className="flex items-center justify-between bg-gray-50 p-4 rounded-lg mb-6">
                <span className="font-medium text-gray-700">Total Bobot:</span>
                <span className={`font-bold ${weights.latihan + weights.tugas + weights.ujian === 100 ? 'text-green-600' : 'text-red-600'}`}>
                  {weights.latihan + weights.tugas + weights.ujian}%
                </span>
              </div>

              <button 
                onClick={handleSaveSettings}
                className="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 flex items-center gap-2"
              >
                <Save size={18} /> Simpan Pengaturan
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
