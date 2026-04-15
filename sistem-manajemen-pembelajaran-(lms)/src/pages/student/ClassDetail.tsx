import React, { useEffect, useState, useRef } from 'react';
import { useParams, Link } from 'react-router-dom';
import { FileText, ArrowLeft, Calendar, CheckSquare, ChevronDown, ChevronUp, MessageCircle, Send, X, Book, Bot } from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useReference } from '../../context/ReferenceContext';

interface Meeting {
  id: number;
  title: string;
  description: string;
  date: string;
  learning_model?: string;
  learning_syntax?: string;
}

interface Material {
  id: number;
  meeting_id: number;
  title: string;
  content: string;
  type: string;
}

interface Assignment {
  id: number;
  meeting_id: number;
  title: string;
  description: string;
  due_date: string;
  type: string;
  work_type: string;
}

export default function ClassDetail() {
  const { id } = useParams();
  const { openReferenceModal, openRAGChatbot } = useReference();
  const [meetings, setMeetings] = useState<Meeting[]>([]);
  const [materials, setMaterials] = useState<Material[]>([]);
  const [assignments, setAssignments] = useState<Assignment[]>([]);
  const [loading, setLoading] = useState(true);
  const [expandedMeeting, setExpandedMeeting] = useState<number | null>(null);
  
  // Chat State
  const { user } = useAuth();
  const [showChat, setShowChat] = useState(false);
  const [chatTab, setChatTab] = useState<'group' | 'personal'>('group');
  const [messages, setMessages] = useState<any[]>([]);
  const [newMessage, setNewMessage] = useState('');
  const messagesEndRef = useRef<HTMLDivElement>(null);
  const contentRef = useRef<HTMLDivElement>(null);
  const [lecturerId, setLecturerId] = useState<number | null>(null);
  const [studentGroup, setStudentGroup] = useState<{id: number, name: string} | null>(null);

  const [activeTab, setActiveTab] = useState<'materials' | 'assignments' | 'attendance'>('materials');

  const scrollToContent = () => {
    setTimeout(() => {
      contentRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
  };

  const [attendanceCode, setAttendanceCode] = useState('');
  const [submittingAttendance, setSubmittingAttendance] = useState(false);

  const handleSubmitAttendance = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!attendanceCode.trim()) return;

    setSubmittingAttendance(true);
    try {
      const res = await fetch('/api/attendance/submit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          student_id: user?.id,
          code: attendanceCode.trim().toUpperCase(),
          class_id: id
        })
      });

      if (res.ok) {
        alert('Presensi berhasil!');
        setAttendanceCode('');
      } else {
        const data = await res.json();
        alert(data.error || 'Gagal melakukan presensi');
      }
    } catch (e) {
      console.error(e);
      alert('Terjadi kesalahan koneksi');
    } finally {
      setSubmittingAttendance(false);
    }
  };

  const availableTools = [
    { id: 'material', label: 'Buka Materi', icon: FileText, action: () => { setActiveTab('materials'); scrollToContent(); } },
    { id: 'assignment', label: 'Kerjakan Tugas', icon: CheckSquare, action: () => { setActiveTab('assignments'); scrollToContent(); } },
    { id: 'group_chat', label: 'Diskusi Kelompok', icon: MessageCircle, action: () => { setShowChat(true); setChatTab('group'); } },
    { id: 'class_chat', label: 'Diskusi Kelas', icon: MessageCircle, action: () => { setShowChat(true); setChatTab('group'); } }
  ];

  useEffect(() => {
    fetchData();
    if (user && id) fetchStudentGroup();
  }, [id, user]);

  const fetchStudentGroup = async () => {
    const res = await fetch(`/api/classes/${id}/students/${user?.id}/group`);
    if (res.ok) {
      const group = await res.json();
      setStudentGroup(group);
    }
  };

  useEffect(() => {
    if (showChat) {
      fetchMessages();
      const interval = setInterval(fetchMessages, 3000);
      return () => clearInterval(interval);
    }
  }, [showChat, chatTab]);

  useEffect(() => {
    if (showChat) scrollToBottom();
  }, [messages, showChat]);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
  };

  const fetchData = async () => {
    try {
      setLoading(true);
      const [resMeetings, resContent, resClass] = await Promise.all([
        fetch(`/api/classes/${id}/meetings`),
        fetch(`/api/classes/${id}/details`),
        fetch(`/api/classes/${id}`)
      ]);

      if (resMeetings.ok && resContent.ok && resClass.ok) {
        setMeetings(await resMeetings.json());
        const contentData = await resContent.json();
        setMaterials(contentData.materials);
        setAssignments(contentData.assignments);
        
        const classData = await resClass.json();
        setLecturerId(classData.lecturer_id);
      }
    } catch (error) {
      console.error('Failed to fetch class details', error);
    } finally {
      setLoading(false);
    }
  };

  const fetchMessages = async () => {
    if (chatTab === 'group') {
      let url = `/api/chat/group/${id}`;
      if (studentGroup) {
        url += `?group_id=${studentGroup.id}`;
      }
      const res = await fetch(url);
      if (res.ok) setMessages(await res.json());
    } else if (chatTab === 'personal' && lecturerId) {
      const res = await fetch(`/api/chat/personal/${lecturerId}?user_id=${user?.id}`);
      if (res.ok) setMessages(await res.json());
    }
  };

  const handleSendMessage = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newMessage.trim()) return;

    const payload = {
      sender_id: user?.id,
      content: newMessage,
      receiver_id: chatTab === 'personal' ? lecturerId : null,
      class_id: id,
      group_id: chatTab === 'group' && studentGroup ? studentGroup.id : null
    };

    const res = await fetch('/api/chat/send', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    if (res.ok) {
      setNewMessage('');
      fetchMessages();
    }
  };

  const toggleMeeting = (meetingId: number) => {
    setExpandedMeeting(expandedMeeting === meetingId ? null : meetingId);
  };

  if (loading) return <div className="p-8">Loading...</div>;

  return (
    <div className="pb-20 relative min-h-screen">
      <div className="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <Link to="/student/classes" className="flex items-center text-gray-600 hover:text-indigo-600 transition-colors">
          <ArrowLeft size={20} className="mr-2" /> Kembali ke Kelas
        </Link>
        
        <div className="flex gap-2">
          <button 
            onClick={openReferenceModal}
            className="flex items-center gap-2 px-4 py-2 bg-white border border-indigo-100 rounded-xl text-indigo-600 font-bold text-sm hover:bg-indigo-50 transition-all shadow-sm active:scale-95"
          >
            <Book size={16} />
            Referensi
          </button>
          <button 
            onClick={openRAGChatbot}
            className="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all shadow-md shadow-indigo-100 active:scale-95"
          >
            <Bot size={16} />
            Tanya AI
          </button>
        </div>
      </div>
      
      <h1 className="text-2xl font-bold text-gray-800 mb-6">Detail Kelas</h1>

      {/* Tabs */}
      <div className="flex border-b border-gray-200 mb-6 overflow-x-auto">
        <button
          onClick={() => setActiveTab('materials')}
          className={`px-6 py-3 font-medium text-sm flex items-center gap-2 border-b-2 transition whitespace-nowrap ${activeTab === 'materials' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'}`}
        >
          <FileText size={18} /> Materi Pembelajaran
        </button>
        <button
          onClick={() => setActiveTab('assignments')}
          className={`px-6 py-3 font-medium text-sm flex items-center gap-2 border-b-2 transition whitespace-nowrap ${activeTab === 'assignments' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'}`}
        >
          <CheckSquare size={18} /> Latihan & Tugas
        </button>
        <button
          onClick={() => setActiveTab('attendance')}
          className={`px-6 py-3 font-medium text-sm flex items-center gap-2 border-b-2 transition whitespace-nowrap ${activeTab === 'attendance' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'}`}
        >
          <Calendar size={18} /> Presensi
        </button>
      </div>

      {activeTab === 'attendance' ? (
        <div className="bg-white p-8 rounded-2xl shadow-sm border border-gray-200 text-center max-w-md mx-auto animate-in fade-in zoom-in-95 duration-300">
          <div className="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <Calendar className="text-indigo-600" size={32} />
          </div>
          <h2 className="text-xl font-bold text-gray-800 mb-2">Input Kode Presensi</h2>
          <p className="text-gray-500 text-sm mb-8">Masukkan kode yang diberikan oleh dosen di dalam chat kelas.</p>
          
          <form onSubmit={handleSubmitAttendance} className="space-y-4">
            <input
              type="text"
              value={attendanceCode}
              onChange={e => setAttendanceCode(e.target.value.toUpperCase())}
              placeholder="CONTOH: AB123"
              className="w-full p-4 text-center text-2xl font-mono font-bold tracking-widest border-2 border-gray-100 rounded-2xl focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none bg-gray-50"
              required
            />
            <button
              type="submit"
              disabled={submittingAttendance}
              className="w-full bg-indigo-600 text-white p-4 rounded-2xl font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-[0.98] disabled:opacity-50"
            >
              {submittingAttendance ? 'Memproses...' : 'Kirim Presensi'}
            </button>
          </form>
        </div>
      ) : (
        <div className="space-y-4">
        {meetings.length === 0 ? (
          <div className="text-center py-12 bg-white rounded-xl border border-dashed border-gray-300">
            <p className="text-gray-500">Belum ada pertemuan yang dijadwalkan.</p>
          </div>
        ) : (
          meetings.map((meeting, index) => {
            const meetingMaterials = materials.filter(m => Number(m.meeting_id) === Number(meeting.id));
            const meetingAssignments = assignments.filter(a => Number(a.meeting_id) === Number(meeting.id));
            
            // Filter content based on active tab
            const hasContent = activeTab === 'materials' ? meetingMaterials.length > 0 : meetingAssignments.length > 0;
            
            // If no content for this tab in this meeting, maybe skip or show empty state?
            // Let's show the meeting header but indicate empty content if expanded.
            
            const isExpanded = expandedMeeting === meeting.id || (index === 0 && expandedMeeting === null);

            return (
              <div key={meeting.id} className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <button 
                  onClick={() => toggleMeeting(meeting.id)}
                  className="w-full p-6 flex justify-between items-center bg-gray-50 hover:bg-gray-100 transition text-left"
                >
                  <div className="flex gap-4 items-center">
                    <div className="bg-white w-12 h-12 rounded-lg flex items-center justify-center border border-gray-200 font-bold text-indigo-600 shadow-sm">
                      {index + 1}
                    </div>
                    <div>
                      <h3 className="font-bold text-gray-800 text-lg">{meeting.title}</h3>
                      <p className="text-sm text-gray-500">{new Date(meeting.date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' })}</p>
                    </div>
                  </div>
                  {isExpanded ? <ChevronUp className="text-gray-400" /> : <ChevronDown className="text-gray-400" />}
                </button>

                {isExpanded && (
                  <div className="p-6 border-t border-gray-100 animate-in slide-in-from-top-2 duration-200">
                    
                    {meeting.learning_model && meeting.learning_model !== 'none' ? (
                      <div className="mb-8 space-y-6">
                        <div className="flex items-center gap-2 mb-4">
                          <span className="bg-indigo-100 text-indigo-800 text-xs px-3 py-1 rounded-full font-bold uppercase tracking-wide">
                            Model: {meeting.learning_model}
                          </span>
                        </div>
                        
                        {(() => {
                          let syntax: any[] = [];
                          try { 
                            const parsed = JSON.parse(meeting.learning_syntax || '[]');
                            if (Array.isArray(parsed)) {
                              syntax = parsed;
                            } else {
                              // Handle old object format for backward compatibility
                              syntax = Object.entries(parsed).map(([title, data]: [string, any]) => ({
                                title,
                                ...(data as any)
                              }));
                            }
                          } catch (e) {
                            console.error("Error parsing syntax", e);
                          }

                          if (syntax.length === 0) return null;

                          return syntax.map((stepData, idx) => (
                            <div key={idx} className="relative pl-6 border-l-2 border-indigo-200 pb-2 last:pb-0">
                              <div className="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-indigo-500 border-4 border-white shadow-sm"></div>
                              <h4 className="font-bold text-gray-800 text-lg leading-none mb-3">{stepData.title}</h4>
                              
                              {/* Sub-steps */}
                              {stepData.subSteps && stepData.subSteps.length > 0 && (
                                <ul className="space-y-2 mb-4">
                                  {stepData.subSteps.map((sub: string, i: number) => (
                                    <li key={i} className="text-gray-600 text-sm flex gap-3 bg-gray-50 p-3 rounded-lg border border-gray-100">
                                      <div className="w-1.5 h-1.5 bg-indigo-400 rounded-full mt-1.5 flex-shrink-0" />
                                      {sub}
                                    </li>
                                  ))}
                                </ul>
                              )}

                              {/* Tools / Menu Fitur */}
                              {stepData.tools && stepData.tools.length > 0 && (
                                <div className="flex flex-wrap gap-3 mt-3">
                                  {stepData.tools.map((toolId: string) => {
                                    const tool = availableTools.find(t => t.id === toolId);
                                    if (!tool) return null;
                                    const Icon = tool.icon;
                                    return (
                                      <button 
                                        key={toolId}
                                        onClick={tool.action}
                                        className="flex items-center gap-2 px-4 py-2 bg-white border border-indigo-100 shadow-sm rounded-lg text-sm font-medium text-indigo-700 hover:bg-indigo-50 hover:border-indigo-300 transition group"
                                      >
                                        <div className="bg-indigo-100 p-1.5 rounded-md group-hover:bg-indigo-200 transition">
                                          <Icon size={16} className="text-indigo-600" />
                                        </div>
                                        {tool.label}
                                      </button>
                                    );
                                  })}
                                </div>
                              )}
                            </div>
                          ));
                        })()}
                      </div>
                    ) : (
                      <p className="text-gray-600 mb-6 text-sm leading-relaxed">{meeting.description}</p>
                    )}
                    
                    {/* Content Section */}
                    <div ref={isExpanded ? contentRef : null} className="scroll-mt-24 pt-4 border-t border-gray-100 mt-6">
                      {activeTab === 'materials' && (
                        <div>
                          {meetingMaterials.length === 0 ? (
                            <div className="text-center py-8 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                              <p className="text-gray-500 font-medium">Materi tidak tersedia</p>
                              <p className="text-xs text-gray-400 mt-1">Dosen belum mengunggah materi untuk pertemuan ini.</p>
                            </div>
                          ) : (
                            <div className="space-y-3">
                              {meetingMaterials.map(m => (
                                <div key={m.id} className="bg-blue-50 p-4 rounded-lg border border-blue-100">
                                  <h5 className="font-semibold text-blue-900 flex items-center gap-2">
                                    <FileText size={16} /> {m.title}
                                  </h5>
                                  <div className="text-sm text-blue-800 mt-2 whitespace-pre-wrap">
                                    {m.content.startsWith('http') ? (
                                      <a href={m.content} target="_blank" rel="noopener noreferrer" className="text-blue-600 underline hover:text-blue-800 flex items-center gap-1">
                                        Buka Link <FileText size={12} />
                                      </a>
                                    ) : (
                                      m.content
                                    )}
                                  </div>
                                </div>
                              ))}
                            </div>
                          )}
                        </div>
                      )}

                      {activeTab === 'assignments' && (
                        <div>
                          {meetingAssignments.length === 0 ? (
                            <div className="text-center py-8 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                              <p className="text-gray-500 font-medium">Latihan & Tugas tidak tersedia</p>
                              <p className="text-xs text-gray-400 mt-1">Dosen belum menetapkan tugas untuk pertemuan ini.</p>
                            </div>
                          ) : (
                            <div className="space-y-3">
                              {meetingAssignments.map(a => (
                              <div key={a.id} className="bg-white p-4 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition">
                                <div className="flex justify-between items-start mb-3">
                                  <div>
                                    <h5 className="font-bold text-gray-800 text-lg">{a.title}</h5>
                                    <div className="flex gap-2 mt-1">
                                      <span className={`text-xs px-2 py-0.5 rounded border font-bold uppercase ${a.type === 'ujian' ? 'bg-red-50 text-red-600 border-red-100' : 'bg-orange-50 text-orange-600 border-orange-100'}`}>
                                        {a.type}
                                      </span>
                                      <span className="text-xs px-2 py-0.5 rounded border bg-blue-50 text-blue-600 border-blue-100 font-bold uppercase">
                                        {a.work_type || 'Individu'}
                                      </span>
                                    </div>
                                  </div>
                                  {a.due_date && (
                                    <div className="text-right">
                                      <span className="text-xs text-gray-500 block">Tenggat Waktu</span>
                                      <span className="text-xs font-medium text-red-600">
                                        {new Date(a.due_date).toLocaleString('id-ID')}
                                      </span>
                                    </div>
                                  )}
                                </div>
                                
                                <p className="text-sm text-gray-600 mb-4 line-clamp-2">{a.description}</p>
                                
                                <Link 
                                  to={`/student/assignments/${a.id}`}
                                  className="inline-flex items-center justify-center w-full bg-indigo-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition gap-2"
                                >
                                  <CheckSquare size={16} /> Kerjakan Soal
                                </Link>
                              </div>
                            ))}
                          </div>
                        )}
                      </div>
                    )}
                    </div>
                  </div>
                )}
              </div>
            );
          })
        )}
      </div>
      )}

      {/* Floating Chat Button */}
      <button
        onClick={() => setShowChat(!showChat)}
        className="fixed bottom-6 right-6 bg-indigo-600 text-white p-4 rounded-full shadow-lg hover:bg-indigo-700 transition z-50"
      >
        {showChat ? <X size={24} /> : <MessageCircle size={24} />}
      </button>

      {/* Chat Widget */}
      {showChat && (
        <div className="fixed bottom-24 right-6 w-96 h-[500px] bg-white rounded-xl shadow-2xl border border-gray-200 flex flex-col z-50 overflow-hidden animate-in slide-in-from-bottom-5 duration-200">
          <div className="bg-indigo-600 p-4 text-white flex justify-between items-center">
            <div>
              <h3 className="font-bold">Diskusi {chatTab === 'group' && studentGroup ? 'Kelompok' : 'Kelas'}</h3>
              {chatTab === 'group' && studentGroup && <p className="text-xs text-indigo-200">{studentGroup.name}</p>}
            </div>
            <div className="flex gap-2 text-xs">
              <button 
                onClick={() => setChatTab('group')}
                className={`px-3 py-1 rounded-full ${chatTab === 'group' ? 'bg-white text-indigo-600 font-bold' : 'bg-indigo-700 text-indigo-100'}`}
              >
                Grup
              </button>
              <button 
                onClick={() => setChatTab('personal')}
                className={`px-3 py-1 rounded-full ${chatTab === 'personal' ? 'bg-white text-indigo-600 font-bold' : 'bg-indigo-700 text-indigo-100'}`}
              >
                Tanya Dosen
              </button>
            </div>
          </div>

          <div className="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50">
            {messages.length === 0 ? (
              <p className="text-center text-gray-400 text-sm mt-10">Belum ada pesan.</p>
            ) : (
              messages.map((msg, idx) => {
                const isMe = msg.sender_id === user?.id;
                return (
                  <div key={idx} className={`flex ${isMe ? 'justify-end' : 'justify-start'}`}>
                    <div className={`max-w-[80%] rounded-2xl p-3 shadow-sm ${isMe ? 'bg-indigo-600 text-white rounded-br-none' : 'bg-white text-gray-800 rounded-bl-none border border-gray-200'}`}>
                      {!isMe && chatTab === 'group' && (
                        <div className="text-xs font-bold text-indigo-600 mb-1">{msg.sender_name}</div>
                      )}
                      <p className="text-sm">{msg.content}</p>
                      <div className={`text-[10px] mt-1 text-right ${isMe ? 'text-indigo-200' : 'text-gray-400'}`}>
                        {new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                      </div>
                    </div>
                  </div>
                );
              })
            )}
            <div ref={messagesEndRef} />
          </div>

          <form onSubmit={handleSendMessage} className="p-3 bg-white border-t border-gray-200 flex gap-2">
            <input
              value={newMessage}
              onChange={e => setNewMessage(e.target.value)}
              placeholder="Tulis pesan..."
              className="flex-1 border border-gray-300 rounded-full px-4 py-2 text-sm focus:outline-none focus:border-indigo-500"
            />
            <button 
              type="submit" 
              disabled={!newMessage.trim()}
              className="bg-indigo-600 text-white p-2 rounded-full hover:bg-indigo-700 disabled:opacity-50 transition"
            >
              <Send size={18} />
            </button>
          </form>
        </div>
      )}
    </div>
  );
}
