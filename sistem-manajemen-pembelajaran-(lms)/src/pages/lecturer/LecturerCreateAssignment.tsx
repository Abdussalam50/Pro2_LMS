import React, { useEffect, useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import { Plus, Save, Trash2, CheckSquare, AlignLeft, Calendar, Layout, Users, BookOpen, ChevronDown, ChevronRight } from 'lucide-react';

interface Question {
  question_text: string;
  question_type: 'essay' | 'multiple_choice';
  options: string[];
  correct_answer: string;
  points: number;
}

interface MainQuestion {
  title: string;
  description: string;
  questions: Question[];
}

interface Class {
  id: number;
  name: string;
  code: string;
  course_name: string;
}

export default function LecturerCreateAssignment() {
  const { user } = useAuth();
  const [classes, setClasses] = useState<Class[]>([]);
  const [selectedClassIds, setSelectedClassIds] = useState<number[]>([]);
  const [meetingIndex, setMeetingIndex] = useState(0); // 0 = Pertemuan 1, etc.
  
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    due_date: '',
    type: 'latihan',
    work_type: 'individu'
  });

  const [mainQuestions, setMainQuestions] = useState<MainQuestion[]>([
    { 
      title: 'Bagian 1', 
      description: '', 
      questions: [{ question_text: '', question_type: 'essay', options: [], correct_answer: '', points: 10 }] 
    }
  ]);

  useEffect(() => {
    if (user) fetchClasses();
  }, [user]);

  const fetchClasses = async () => {
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

  const handleClassToggle = (id: number) => {
    setSelectedClassIds(prev => 
      prev.includes(id) ? prev.filter(c => c !== id) : [...prev, id]
    );
  };

  // Main Question Handlers
  const addMainQuestion = () => {
    setMainQuestions([...mainQuestions, { 
      title: `Bagian ${mainQuestions.length + 1}`, 
      description: '', 
      questions: [{ question_text: '', question_type: 'essay', options: [], correct_answer: '', points: 10 }] 
    }]);
  };

  const removeMainQuestion = (index: number) => {
    const newMQ = [...mainQuestions];
    newMQ.splice(index, 1);
    setMainQuestions(newMQ);
  };

  const updateMainQuestion = (index: number, field: keyof MainQuestion, value: any) => {
    const newMQ = [...mainQuestions];
    newMQ[index] = { ...newMQ[index], [field]: value };
    setMainQuestions(newMQ);
  };

  // Sub Question Handlers
  const addQuestion = (mqIndex: number) => {
    const newMQ = [...mainQuestions];
    newMQ[mqIndex].questions.push({ question_text: '', question_type: 'essay', options: [], correct_answer: '', points: 10 });
    setMainQuestions(newMQ);
  };

  const removeQuestion = (mqIndex: number, qIndex: number) => {
    const newMQ = [...mainQuestions];
    newMQ[mqIndex].questions.splice(qIndex, 1);
    setMainQuestions(newMQ);
  };

  const updateQuestion = (mqIndex: number, qIndex: number, field: keyof Question, value: any) => {
    const newMQ = [...mainQuestions];
    newMQ[mqIndex].questions[qIndex] = { ...newMQ[mqIndex].questions[qIndex], [field]: value };
    setMainQuestions(newMQ);
  };

  const handleOptionChange = (mqIndex: number, qIndex: number, oIndex: number, value: string) => {
    const newMQ = [...mainQuestions];
    const newOptions = [...newMQ[mqIndex].questions[qIndex].options];
    newOptions[oIndex] = value;
    newMQ[mqIndex].questions[qIndex].options = newOptions;
    setMainQuestions(newMQ);
  };

  const addOption = (mqIndex: number, qIndex: number) => {
    const newMQ = [...mainQuestions];
    newMQ[mqIndex].questions[qIndex].options.push('');
    setMainQuestions(newMQ);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (selectedClassIds.length === 0) {
      alert('Pilih minimal satu kelas');
      return;
    }

    const payload = {
      class_ids: selectedClassIds,
      meeting_index: meetingIndex,
      ...formData,
      main_questions: mainQuestions
    };

    const res = await fetch('/api/assignments/create-batch', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    if (res.ok) {
      alert('Soal berhasil dibuat dan didistribusikan');
      setFormData({ title: '', description: '', due_date: '', type: 'latihan', work_type: 'individu' });
      setMainQuestions([{ title: 'Bagian 1', description: '', questions: [{ question_text: '', question_type: 'essay', options: [], correct_answer: '', points: 10 }] }]);
      setSelectedClassIds([]);
    } else {
      alert('Gagal membuat soal');
    }
  };

  const totalPoints = mainQuestions.reduce((acc, mq) => acc + mq.questions.reduce((qAcc, q) => qAcc + q.points, 0), 0);
  const totalQuestions = mainQuestions.reduce((acc, mq) => acc + mq.questions.length, 0);

  return (
    <div className="max-w-7xl mx-auto pb-32 px-4 sm:px-6 lg:px-8">
      <div className="flex items-center gap-3 mb-8 mt-6">
        <div className="p-3 bg-indigo-100 rounded-xl text-indigo-600">
          <BookOpen size={24} />
        </div>
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Input Soal</h1>
          <p className="text-gray-500 text-sm">Buat dan distribusikan tugas dengan struktur Master Soal - Main Soal - Soal</p>
        </div>
      </div>
      
      <form onSubmit={handleSubmit} className="space-y-8">
        <div className="grid grid-cols-1 lg:grid-cols-4 gap-8">
          {/* Left Column: Master Soal (General Info) */}
          <div className="lg:col-span-1 space-y-6">
            <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 sticky top-4">
              <h2 className="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <Layout size={18} className="text-indigo-500" /> Master Soal
              </h2>
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1.5">Judul Tugas</label>
                  <input 
                    value={formData.title}
                    onChange={e => setFormData({...formData, title: e.target.value})}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                    placeholder="Contoh: Latihan Pertemuan 1"
                    required
                  />
                </div>
                
                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1.5">Tipe</label>
                    <select 
                      value={formData.type}
                      onChange={e => setFormData({...formData, type: e.target.value})}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                    >
                      <option value="latihan">Latihan</option>
                      <option value="tugas">Tugas</option>
                      <option value="ujian">Ujian</option>
                    </select>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1.5">Pengerjaan</label>
                    <select 
                      value={formData.work_type}
                      onChange={e => setFormData({...formData, work_type: e.target.value})}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                    >
                      <option value="individu">Individu</option>
                      <option value="kelompok">Kelompok</option>
                    </select>
                  </div>
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1.5">Deskripsi</label>
                  <textarea 
                    value={formData.description}
                    onChange={e => setFormData({...formData, description: e.target.value})}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition h-24 resize-none"
                    placeholder="Instruksi pengerjaan..."
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1.5">Tenggat Waktu</label>
                  <div className="relative">
                    <Calendar className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" size={16} />
                    <input 
                      type="datetime-local"
                      value={formData.due_date}
                      onChange={e => setFormData({...formData, due_date: e.target.value})}
                      className="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                    />
                  </div>
                </div>

                <div className="pt-4 border-t">
                  <label className="block text-sm font-medium text-gray-700 mb-2">Distribusi Kelas</label>
                  <div className="space-y-2 max-h-48 overflow-y-auto pr-1 custom-scrollbar">
                    {classes.map(cls => (
                      <label key={cls.id} className={`flex items-center p-2 rounded-lg border cursor-pointer transition-all ${selectedClassIds.includes(cls.id) ? 'bg-indigo-50 border-indigo-500 shadow-sm' : 'border-gray-200 hover:bg-gray-50'}`}>
                        <input 
                          type="checkbox"
                          checked={selectedClassIds.includes(cls.id)}
                          onChange={() => handleClassToggle(cls.id)}
                          className="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                        />
                        <div className="flex-1 min-w-0">
                          <div className="font-medium text-sm text-gray-900 truncate">{cls.name}</div>
                        </div>
                      </label>
                    ))}
                  </div>
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1.5">Pertemuan Ke-</label>
                  <select 
                    value={meetingIndex}
                    onChange={e => setMeetingIndex(Number(e.target.value))}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                  >
                    {[...Array(16)].map((_, i) => (
                      <option key={i} value={i}>Pertemuan {i + 1}</option>
                    ))}
                  </select>
                </div>
              </div>
            </div>
          </div>

          {/* Right Column: Main Soal & Soal */}
          <div className="lg:col-span-3 space-y-8">
            <div className="flex justify-between items-center bg-white p-4 rounded-2xl shadow-sm border border-gray-200 sticky top-4 z-10">
              <h2 className="text-lg font-bold text-gray-800 flex items-center gap-2">
                <CheckSquare size={18} className="text-indigo-500" /> Daftar Main Soal
                <span className="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">{mainQuestions.length}</span>
              </h2>
              <button type="button" onClick={addMainQuestion} className="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition flex items-center gap-2 text-sm font-medium shadow-sm">
                <Plus size={16} /> Tambah Main Soal
              </button>
            </div>

            {mainQuestions.map((mq, mqIndex) => (
              <div key={mqIndex} className="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                {/* Main Soal Header */}
                <div className="bg-gray-50 p-6 border-b border-gray-200">
                  <div className="flex justify-between items-start mb-4">
                    <div className="flex-1 mr-4">
                      <input 
                        value={mq.title}
                        onChange={e => updateMainQuestion(mqIndex, 'title', e.target.value)}
                        className="text-lg font-bold bg-transparent border-none focus:ring-0 p-0 w-full text-gray-800 placeholder-gray-400"
                        placeholder="Judul Main Soal (Misal: Bagian 1 - Reading)"
                      />
                      <textarea 
                        value={mq.description}
                        onChange={e => updateMainQuestion(mqIndex, 'description', e.target.value)}
                        className="w-full mt-2 bg-white px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition text-sm"
                        placeholder="Deskripsi Main Soal (Misal: Baca teks berikut untuk menjawab soal 1-5...)"
                        rows={2}
                      />
                    </div>
                    <button type="button" onClick={() => removeMainQuestion(mqIndex)} className="text-gray-400 hover:text-red-500 p-2 hover:bg-red-50 rounded-lg transition">
                      <Trash2 size={20} />
                    </button>
                  </div>
                  
                  <div className="flex justify-between items-center">
                    <div className="text-sm text-gray-500 font-medium">
                      {mq.questions.length} Soal
                    </div>
                    <button type="button" onClick={() => addQuestion(mqIndex)} className="text-sm bg-white border border-indigo-200 text-indigo-600 px-3 py-1.5 rounded-lg hover:bg-indigo-50 font-medium flex items-center gap-1 shadow-sm">
                      <Plus size={14} /> Tambah Soal
                    </button>
                  </div>
                </div>

                {/* Sub Questions List */}
                <div className="p-6 space-y-6 bg-white">
                  {mq.questions.length === 0 ? (
                    <p className="text-center text-gray-400 italic py-4">Belum ada soal di bagian ini.</p>
                  ) : (
                    mq.questions.map((q, qIndex) => (
                      <div key={qIndex} className="pl-4 border-l-4 border-indigo-100 relative group">
                        <div className="absolute top-0 right-0 opacity-0 group-hover:opacity-100 transition-opacity">
                          <button type="button" onClick={() => removeQuestion(mqIndex, qIndex)} className="text-gray-400 hover:text-red-500 p-1">
                            <Trash2 size={16} />
                          </button>
                        </div>

                        <div className="space-y-3">
                          <div className="flex gap-3">
                            <span className="font-bold text-indigo-600 text-sm mt-2">{qIndex + 1}.</span>
                            <div className="flex-1">
                              <textarea 
                                value={q.question_text}
                                onChange={e => updateQuestion(mqIndex, qIndex, 'question_text', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition text-sm min-h-[60px]"
                                placeholder="Pertanyaan..."
                                required
                              />
                            </div>
                          </div>

                          <div className="flex flex-wrap gap-4 pl-7">
                            <div className="w-40">
                              <select 
                                value={q.question_type}
                                onChange={e => updateQuestion(mqIndex, qIndex, 'question_type', e.target.value)}
                                className="w-full px-2 py-1.5 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-xs"
                              >
                                <option value="essay">Essay</option>
                                <option value="multiple_choice">Pilihan Ganda</option>
                              </select>
                            </div>
                            <div className="w-24 flex items-center gap-2">
                              <span className="text-xs text-gray-500">Poin:</span>
                              <input 
                                type="number"
                                value={q.points}
                                onChange={e => updateQuestion(mqIndex, qIndex, 'points', Number(e.target.value))}
                                className="w-full px-2 py-1.5 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-xs"
                                placeholder="0"
                              />
                            </div>
                          </div>

                          {q.question_type === 'multiple_choice' && (
                            <div className="pl-7 space-y-2">
                              {q.options.map((opt, optIndex) => (
                                <div key={optIndex} className="flex items-center gap-2">
                                  <input 
                                    type="radio" 
                                    name={`correct-${mqIndex}-${qIndex}`}
                                    checked={q.correct_answer === opt}
                                    onChange={() => updateQuestion(mqIndex, qIndex, 'correct_answer', opt)}
                                    className="w-3 h-3 text-indigo-600 cursor-pointer"
                                  />
                                  <input 
                                    value={opt}
                                    onChange={e => handleOptionChange(mqIndex, qIndex, optIndex, e.target.value)}
                                    className="flex-1 px-2 py-1 border border-gray-300 rounded text-xs"
                                    placeholder={`Opsi ${optIndex + 1}`}
                                  />
                                </div>
                              ))}
                              <button type="button" onClick={() => addOption(mqIndex, qIndex)} className="text-xs text-indigo-600 hover:underline font-medium">
                                + Tambah Opsi
                              </button>
                            </div>
                          )}
                        </div>
                      </div>
                    ))
                  )}
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Floating Bottom Bar */}
        <div className="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-20">
          <div className="max-w-7xl mx-auto flex justify-between items-center px-4 sm:px-6 lg:px-8">
            <div className="text-sm text-gray-500 hidden sm:block">
              Total Main Soal: <span className="font-bold text-gray-800">{mainQuestions.length}</span> | 
              Total Soal: <span className="font-bold text-gray-800">{totalQuestions}</span> | 
              Total Poin: <span className="font-bold text-gray-800">{totalPoints}</span>
            </div>
            <button type="submit" className="bg-indigo-600 text-white px-8 py-3 rounded-xl shadow-lg hover:bg-indigo-700 font-bold flex items-center gap-2 transition transform hover:scale-105 active:scale-95">
              <Save size={20} /> Simpan & Distribusikan
            </button>
          </div>
        </div>
      </form>
    </div>
  );
}
