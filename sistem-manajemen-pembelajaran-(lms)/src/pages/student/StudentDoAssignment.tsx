import React, { useEffect, useState } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { ArrowLeft, Save, CheckCircle, ChevronLeft, ChevronRight } from 'lucide-react';
import { useAuth } from '../../context/AuthContext';

interface Question {
  id: number;
  question_text: string;
  question_type: 'essay' | 'multiple_choice';
  options: string; // JSON string
  points: number;
}

interface MainQuestion {
  id: number;
  title: string;
  description: string;
  questions: Question[];
}

interface Assignment {
  id: number;
  title: string;
  description: string;
  type: string;
  work_type: string;
  main_questions: MainQuestion[];
  standalone_questions: Question[];
}

export default function StudentDoAssignment() {
  const { id } = useParams(); // assignment id
  const { user } = useAuth();
  const navigate = useNavigate();
  const [assignment, setAssignment] = useState<Assignment | null>(null);
  const [answers, setAnswers] = useState<Record<number, string>>({});
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  
  // Pagination State
  const [currentPage, setCurrentPage] = useState(0);
  const [pages, setPages] = useState<MainQuestion[]>([]);

  useEffect(() => {
    if (id) {
      fetchAssignment();
    }
  }, [id]);

  const fetchAssignment = async () => {
    if (!id) return;
    try {
      const res = await fetch(`/api/assignments/${id}`);
      if (res.ok) {
        const data = await res.json();
        setAssignment(data);
        
        // Prepare pages
        const allPages: MainQuestion[] = [...(data.main_questions || [])];
        
        // If there are standalone questions, add them as a separate page
        if (data.standalone_questions && data.standalone_questions.length > 0) {
          allPages.push({
            id: -1, // temporary ID
            title: 'Soal Tambahan',
            description: 'Soal-soal berikut tidak termasuk dalam bagian khusus.',
            questions: data.standalone_questions
          });
        }

        // Fallback for old structure if both are empty but 'questions' exists (though API should handle this)
        if (allPages.length === 0 && data.questions && data.questions.length > 0) {
           allPages.push({
            id: -1,
            title: 'Daftar Soal',
            description: '',
            questions: data.questions
          });
        }

        setPages(allPages);
      } else {
        console.error('Failed to fetch assignment:', res.status, res.statusText);
      }
    } catch (error) {
      console.error('Failed to fetch assignment', error);
    } finally {
      setLoading(false);
    }
  };

  const handleAnswerChange = (questionId: number, value: string) => {
    setAnswers(prev => ({ ...prev, [questionId]: value }));
  };

  const handleSubmit = async () => {
    console.log('handleSubmit called');
    console.log('Current user:', user);
    console.log('Assignment ID (from params):', id);
    console.log('Current answers:', answers);
    
    // Temporarily removed confirm to debug
    // if (!confirm('Apakah Anda yakin ingin mengumpulkan jawaban ini?')) {
    //   console.log('Submission cancelled by user');
    //   return;
    // }
    
    console.log('Proceeding with submission...');
    setSubmitting(true);
    try {
      if (!user) {
        console.error('User is null');
        alert('Sesi Anda telah berakhir. Silakan login kembali.');
        return;
      }

      if (!user.id) {
        console.error('User ID is missing', user);
        alert('ID Pengguna tidak ditemukan. Silakan login kembali.');
        return;
      }

      if (!id) {
        console.error('Assignment ID (id) is missing from params');
        alert('ID Tugas tidak ditemukan.');
        return;
      }

      const payload = {
        assignment_id: Number(id),
        student_id: user.id,
        answers: Object.entries(answers).map(([qId, text]) => ({
          question_id: Number(qId),
          answer_text: text
        }))
      };

      console.log('Submitting payload:', JSON.stringify(payload, null, 2));

      const res = await fetch('/api/submissions', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
      });

      console.log('Response status:', res.status);

      let data;
      const responseText = await res.text();
      console.log('Raw response text:', responseText);

      try {
        data = JSON.parse(responseText);
      } catch (e) {
        console.error('Failed to parse response JSON:', e);
        data = { error: 'Server returned an invalid response format.', details: responseText.substring(0, 100) };
      }

      if (res.ok) {
        console.log('Submission successful:', data);
        alert('Jawaban berhasil dikumpulkan! Anda akan diarahkan kembali.');
        navigate(-1);
      } else {
        console.error('Submission failed with error:', data);
        alert(`Gagal mengumpulkan jawaban: ${data.error || 'Terjadi kesalahan tidak diketahui.'}\n\nDetail: ${data.details || ''}`);
      }
    } catch (error) {
      console.error('Network or unexpected error during submission:', error);
      alert(`Terjadi kesalahan saat mengirim jawaban: ${error instanceof Error ? error.message : 'Koneksi bermasalah'}`);
    } finally {
      setSubmitting(false);
      console.log('handleSubmit finished');
    }
  };

  const nextPage = () => {
    if (currentPage < pages.length - 1) {
      setCurrentPage(curr => curr + 1);
      window.scrollTo(0, 0);
    }
  };

  const prevPage = () => {
    if (currentPage > 0) {
      setCurrentPage(curr => curr - 1);
      window.scrollTo(0, 0);
    }
  };

  if (loading) return <div className="p-8 text-center">Loading...</div>;
  if (!assignment || pages.length === 0) return <div className="p-8 text-center">Tugas tidak ditemukan atau belum ada soal.</div>;

  const currentMainQuestion = pages[currentPage];

  return (
    <div className="max-w-4xl mx-auto pb-32">
      <button onClick={() => navigate(-1)} className="flex items-center text-gray-600 hover:text-indigo-600 mb-6">
        <ArrowLeft size={20} className="mr-2" /> Kembali
      </button>

      {/* Assignment Header */}
      <div className="bg-white p-8 rounded-xl shadow-sm border border-gray-200 mb-8">
        <div className="flex justify-between items-start mb-4">
          <div>
            <h1 className="text-2xl font-bold text-gray-800">{assignment.title}</h1>
            <div className="flex gap-2 mt-2">
              <span className="px-2 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded uppercase">{assignment.type}</span>
              <span className="px-2 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded uppercase">{assignment.work_type || 'Individu'}</span>
            </div>
          </div>
          <div className="text-right text-sm text-gray-500">
            Halaman <span className="font-bold text-gray-900">{currentPage + 1}</span> dari <span className="font-bold text-gray-900">{pages.length}</span>
          </div>
        </div>
        <p className="text-gray-600 whitespace-pre-wrap">{assignment.description}</p>
      </div>

      <div className="space-y-8">
        {/* Main Question Section */}
        <div className="bg-indigo-50 p-6 rounded-xl border border-indigo-100 mb-6">
          <h2 className="text-xl font-bold text-indigo-900 mb-2">{currentMainQuestion.title}</h2>
          {currentMainQuestion.description && (
            <p className="text-indigo-700 whitespace-pre-wrap">{currentMainQuestion.description}</p>
          )}
        </div>

        {/* Questions List */}
        <div className="space-y-6">
          {currentMainQuestion.questions.map((q, index) => {
            let options: string[] = [];
            try {
              if (q.question_type === 'multiple_choice' && q.options) {
                const parsed = JSON.parse(q.options);
                options = Array.isArray(parsed) ? parsed : [];
              }
            } catch (e) {
              console.error('Error parsing options', e);
            }
            
            return (
              <div key={q.id} className="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <div className="flex gap-4 mb-4">
                  <span className="bg-gray-100 px-3 py-1 rounded font-bold text-gray-600 h-fit">#{index + 1}</span>
                  <div className="flex-1">
                    <p className="font-medium text-gray-800 text-lg mb-2 whitespace-pre-wrap">{q.question_text}</p>
                    <p className="text-xs text-gray-400 mb-4">Poin: {q.points}</p>

                    {q.question_type === 'essay' ? (
                      <textarea
                        value={answers[q.id] || ''}
                        onChange={e => handleAnswerChange(q.id, e.target.value)}
                        className="w-full border border-gray-300 rounded-lg p-3 min-h-[150px] focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Tulis jawaban Anda di sini..."
                      />
                    ) : (
                      <div className="space-y-2">
                        {options.map((opt: string, idx: number) => (
                          <label key={idx} className={`flex items-center p-3 rounded-lg border cursor-pointer transition ${answers[q.id] === opt ? 'bg-indigo-50 border-indigo-500' : 'hover:bg-gray-50 border-gray-200'}`}>
                            <input
                              type="radio"
                              name={`question-${q.id}`}
                              value={opt}
                              checked={answers[q.id] === opt}
                              onChange={() => handleAnswerChange(q.id, opt)}
                              className="mr-3 h-4 w-4 text-indigo-600"
                            />
                            <span>{opt}</span>
                          </label>
                        ))}
                      </div>
                    )}
                  </div>
                </div>
              </div>
            );
          })}
        </div>

        {/* Navigation Bar */}
        <div className="fixed bottom-0 left-0 right-0 bg-white border-t p-4 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] z-20">
          <div className="max-w-4xl mx-auto flex justify-between items-center px-4">
            <button
              type="button"
              onClick={prevPage}
              disabled={currentPage === 0}
              className={`flex items-center gap-2 px-6 py-3 rounded-xl font-bold transition ${currentPage === 0 ? 'text-gray-300 cursor-not-allowed' : 'text-gray-600 hover:bg-gray-100'}`}
            >
              <ChevronLeft size={20} /> Sebelumnya
            </button>

            {currentPage === pages.length - 1 ? (
              <button 
                type="button" 
                onClick={handleSubmit}
                disabled={submitting}
                className="bg-indigo-600 text-white px-8 py-3 rounded-xl shadow-lg hover:bg-indigo-700 font-bold flex items-center gap-2 disabled:opacity-70"
              >
                {submitting ? 'Mengirim...' : <><CheckCircle size={20} /> Kumpulkan Jawaban</>}
              </button>
            ) : (
              <button
                type="button"
                onClick={nextPage}
                className="bg-indigo-600 text-white px-8 py-3 rounded-xl shadow-lg hover:bg-indigo-700 font-bold flex items-center gap-2"
              >
                Selanjutnya <ChevronRight size={20} />
              </button>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
