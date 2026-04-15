import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { ArrowLeft, CheckCircle, XCircle, Eye, Save } from 'lucide-react';

interface Student {
  id: number;
  name: string;
  username: string;
}

interface Submission {
  id: number;
  student_id: number;
  submitted_at: string;
  grade: number | null;
  feedback: string | null;
}

interface Assignment {
  id: number;
  title: string;
  description: string;
  type: string;
  class_id: number;
}

interface Answer {
  id: number;
  question_id: number;
  answer_text: string;
  question_text: string;
  question_type: string;
  points: number;
  correct_answer: string;
}

export default function LecturerAssignmentSubmissions() {
  const { assignmentId } = useParams();
  const [assignment, setAssignment] = useState<Assignment | null>(null);
  const [students, setStudents] = useState<Student[]>([]);
  const [submissions, setSubmissions] = useState<Submission[]>([]);
  const [loading, setLoading] = useState(true);
  
  // Grading Modal State
  const [selectedSubmission, setSelectedSubmission] = useState<Submission | null>(null);
  const [studentAnswers, setStudentAnswers] = useState<Answer[]>([]);
  const [grade, setGrade] = useState<number | ''>('');
  const [feedback, setFeedback] = useState('');
  const [showModal, setShowModal] = useState(false);

  useEffect(() => {
    if (assignmentId) {
      fetchData();
    }
  }, [assignmentId]);

  const fetchData = async () => {
    if (!assignmentId) return;
    try {
      // Fetch Assignment
      const assignRes = await fetch(`/api/assignments/${assignmentId}`);
      if (!assignRes.ok) throw new Error('Failed to fetch assignment');
      const assignData = await assignRes.json();
      setAssignment(assignData);

      // Fetch Students in Class
      const studentsRes = await fetch(`/api/classes/${assignData.class_id}/students`);
      if (!studentsRes.ok) throw new Error('Failed to fetch students');
      const studentsData = await studentsRes.json();
      setStudents(studentsData);

      // Fetch Submissions
      const subsRes = await fetch(`/api/assignments/${assignmentId}/submissions`); 
      if (subsRes.ok) {
        setSubmissions(await subsRes.json());
      }
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  const handleGradeClick = async (submission: Submission) => {
    setSelectedSubmission(submission);
    setGrade(submission.grade !== null ? submission.grade : '');
    setFeedback(submission.feedback || '');
    
    // Fetch answers
    const res = await fetch(`/api/submissions/${submission.id}/details`);
    if (res.ok) {
      const data = await res.json();
      setStudentAnswers(data.answers);
      setShowModal(true);
    }
  };

  const handleSaveGrade = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedSubmission) return;

    try {
      const res = await fetch(`/api/submissions/${selectedSubmission.id}/grade`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ grade: Number(grade), feedback })
      });

      if (res.ok) {
        alert('Nilai berhasil disimpan');
        setShowModal(false);
        fetchData(); // Refresh list
      } else {
        alert('Gagal menyimpan nilai');
      }
    } catch (error) {
      console.error(error);
    }
  };

  if (loading) return <div className="p-8">Loading...</div>;
  if (!assignment) return <div className="p-8">Tugas tidak ditemukan.</div>;

  return (
    <div className="max-w-6xl mx-auto pb-20">
      <Link to={`/lecturer/classes/${assignment.class_id}`} className="flex items-center text-gray-600 hover:text-indigo-600 mb-6">
        <ArrowLeft size={20} className="mr-2" /> Kembali ke Kelas
      </Link>

      <div className="bg-white p-8 rounded-xl shadow-sm border border-gray-200 mb-8">
        <h1 className="text-2xl font-bold text-gray-800 mb-2">{assignment.title}</h1>
        <p className="text-gray-600">{assignment.description}</p>
        <div className="mt-4 flex gap-4 text-sm text-gray-500">
          <span className="bg-indigo-50 text-indigo-700 px-2 py-1 rounded uppercase font-bold text-xs">{assignment.type}</span>
          <span>Total Mahasiswa: {students.length}</span>
          <span>Sudah Mengumpulkan: {submissions.length}</span>
        </div>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table className="w-full text-left">
          <thead className="bg-gray-50 border-b border-gray-200">
            <tr>
              <th className="px-6 py-4 font-semibold text-gray-600">Mahasiswa</th>
              <th className="px-6 py-4 font-semibold text-gray-600">Status</th>
              <th className="px-6 py-4 font-semibold text-gray-600">Waktu Pengumpulan</th>
              <th className="px-6 py-4 font-semibold text-gray-600">Nilai</th>
              <th className="px-6 py-4 font-semibold text-gray-600">Aksi</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {students.map(student => {
              const submission = submissions.find(s => s.student_id === student.id);
              return (
                <tr key={student.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4">
                    <div className="font-medium text-gray-900">{student.name}</div>
                    <div className="text-xs text-gray-500">{student.username}</div>
                  </td>
                  <td className="px-6 py-4">
                    {submission ? (
                      <span className="flex items-center gap-1 text-green-600 text-sm font-medium">
                        <CheckCircle size={16} /> Diserahkan
                      </span>
                    ) : (
                      <span className="flex items-center gap-1 text-gray-400 text-sm">
                        <XCircle size={16} /> Belum
                      </span>
                    )}
                  </td>
                  <td className="px-6 py-4 text-sm text-gray-600">
                    {submission ? new Date(submission.submitted_at).toLocaleString() : '-'}
                  </td>
                  <td className="px-6 py-4 font-bold">
                    {submission?.grade !== null && submission?.grade !== undefined ? (
                      <span className={submission.grade >= 75 ? 'text-green-600' : 'text-orange-600'}>
                        {submission.grade}
                      </span>
                    ) : '-'}
                  </td>
                  <td className="px-6 py-4">
                    {submission ? (
                      <button 
                        onClick={() => handleGradeClick(submission)}
                        className="text-indigo-600 hover:text-indigo-800 font-medium text-sm flex items-center gap-1"
                      >
                        <Eye size={16} /> Periksa & Nilai
                      </button>
                    ) : (
                      <span className="text-gray-300 text-sm italic">Tidak ada aksi</span>
                    )}
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>

      {/* Grading Modal */}
      {showModal && selectedSubmission && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-lg w-full max-w-4xl max-h-[90vh] flex flex-col">
            <div className="p-6 border-b flex justify-between items-center">
              <h3 className="text-xl font-bold">Periksa Jawaban</h3>
              <button onClick={() => setShowModal(false)} className="text-gray-500 hover:text-gray-700">
                <XCircle size={24} />
              </button>
            </div>
            
            <div className="flex-1 overflow-y-auto p-6 bg-gray-50">
              <div className="space-y-6">
                {studentAnswers.map((ans, idx) => (
                  <div key={ans.id} className="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <div className="flex justify-between mb-2">
                      <span className="font-bold text-gray-500">Soal #{idx + 1}</span>
                      <span className="text-xs bg-gray-100 px-2 py-1 rounded">Poin: {ans.points}</span>
                    </div>
                    <p className="text-gray-800 font-medium mb-4 whitespace-pre-wrap">{ans.question_text}</p>
                    
                    <div className="bg-indigo-50 p-4 rounded-lg border border-indigo-100">
                      <p className="text-sm text-indigo-900 font-semibold mb-1">Jawaban Mahasiswa:</p>
                      <p className="text-gray-800 whitespace-pre-wrap">{ans.answer_text}</p>
                    </div>

                    {ans.question_type === 'multiple_choice' && (
                      <div className="mt-2 text-sm text-green-700">
                        Kunci Jawaban: <strong>{ans.correct_answer}</strong>
                      </div>
                    )}
                  </div>
                ))}
              </div>
            </div>

            <div className="p-6 border-t bg-white">
              <form onSubmit={handleSaveGrade} className="flex gap-4 items-end">
                <div className="flex-1">
                  <label className="block text-sm font-medium text-gray-700 mb-1">Nilai Akhir (0-100)</label>
                  <input 
                    type="number" 
                    value={grade}
                    onChange={e => setGrade(Number(e.target.value))}
                    className="border p-2 rounded w-full"
                    min="0" max="100"
                    required
                  />
                </div>
                <div className="flex-[2]">
                  <label className="block text-sm font-medium text-gray-700 mb-1">Feedback (Opsional)</label>
                  <input 
                    type="text" 
                    value={feedback}
                    onChange={e => setFeedback(e.target.value)}
                    className="border p-2 rounded w-full"
                    placeholder="Berikan catatan untuk mahasiswa..."
                  />
                </div>
                <button type="submit" className="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 h-[42px] flex items-center gap-2 font-bold shadow-sm">
                  <Save size={18} /> Simpan Nilai
                </button>
              </form>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
