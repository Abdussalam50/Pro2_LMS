import React, { useEffect, useState, useRef } from 'react';
import { useAuth } from '../../context/AuthContext';
import { Send, User, Users, MessageCircle } from 'lucide-react';

interface Message {
  id: number;
  sender_id: number;
  sender_name: string;
  content: string;
  created_at: string;
}

interface Conversation {
  id: number;
  name: string;
  username: string;
  last_message: string;
  last_message_time: string;
}

interface ClassGroup {
  id: number;
  name: string;
  course_name: string;
}

export default function StudentChat() {
  const { user } = useAuth();
  const [activeTab, setActiveTab] = useState<'personal' | 'group'>('group');
  
  // Personal Chat States (Lecturers)
  const [lecturers, setLecturers] = useState<Conversation[]>([]); // Using Conversation interface for simplicity
  const [selectedLecturer, setSelectedLecturer] = useState<Conversation | null>(null);
  
  // Group Chat States (Classes)
  const [classes, setClasses] = useState<ClassGroup[]>([]);
  const [selectedClass, setSelectedClass] = useState<ClassGroup | null>(null);
  const [studentGroup, setStudentGroup] = useState<{id: number, name: string} | null>(null);
  
  // Messages State
  const [messages, setMessages] = useState<Message[]>([]);
  const [newMessage, setNewMessage] = useState('');
  const messagesEndRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (user) {
      fetchClasses();
      fetchLecturers(); // We need to fetch lecturers the student is associated with
    }
  }, [user]);

  useEffect(() => {
    if (activeTab === 'personal' && selectedLecturer) {
      fetchPersonalMessages();
      const interval = setInterval(fetchPersonalMessages, 3000);
      return () => clearInterval(interval);
    } else if (activeTab === 'group' && selectedClass) {
      // First check if student is in a group for this class
      fetchStudentGroup().then(() => {
        fetchGroupMessages();
      });
      const interval = setInterval(fetchGroupMessages, 3000);
      return () => clearInterval(interval);
    }
  }, [selectedLecturer, selectedClass, activeTab]);

  const fetchStudentGroup = async () => {
    if (!selectedClass || !user) return;
    const res = await fetch(`/api/classes/${selectedClass.id}/students/${user.id}/group`);
    if (res.ok) {
      const group = await res.json();
      setStudentGroup(group);
    } else {
      setStudentGroup(null);
    }
  };

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
  };

  const fetchClasses = async () => {
    const res = await fetch(`/api/student/${user?.id}/classes`);
    if (res.ok) {
      const data = await res.json();
      setClasses(data);
    }
  };

  const fetchLecturers = async () => {
    // For now, let's get lecturers from the classes
    // We can reuse the classes endpoint which returns lecturer_name. 
    // But we need lecturer_id. The /api/student/:id/classes endpoint returns lecturer_name but maybe not ID?
    // Let's check api.ts. It joins users u ON c.lecturer_id = u.id. It selects c.*. 
    // So c.lecturer_id should be there.
    const res = await fetch(`/api/student/${user?.id}/classes`);
    if (res.ok) {
      const data = await res.json();
      // Extract unique lecturers
      const uniqueLecturers = new Map();
      data.forEach((c: any) => {
        if (!uniqueLecturers.has(c.lecturer_id)) {
          uniqueLecturers.set(c.lecturer_id, {
            id: c.lecturer_id,
            name: c.lecturer_name,
            username: 'Dosen', // Placeholder or fetch real username if needed
            last_message: '',
            last_message_time: ''
          });
        }
      });
      setLecturers(Array.from(uniqueLecturers.values()));
    }
  };

  const fetchPersonalMessages = async () => {
    if (!selectedLecturer) return;
    const res = await fetch(`/api/chat/personal/${selectedLecturer.id}?user_id=${user?.id}`);
    if (res.ok) {
      setMessages(await res.json());
      // Mark as read
      await fetch('/api/chat/read', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: user?.id, sender_id: selectedLecturer.id })
      });
    }
  };

  const fetchGroupMessages = async () => {
    if (!selectedClass) return;
    // If studentGroup is set, fetch messages for that group. Otherwise fetch class messages (group_id=null)
    // Note: fetchStudentGroup is async, so inside the interval we rely on the state `studentGroup`
    // But `studentGroup` might not be set immediately on first run. 
    // We'll pass the group_id param if it exists.
    
    // Actually, we should use the state `studentGroup`. 
    // But inside `useEffect`, `fetchStudentGroup` is called.
    
    let url = `/api/chat/group/${selectedClass.id}`;
    if (studentGroup) {
      url += `?group_id=${studentGroup.id}`;
    }
    
    const res = await fetch(url);
    if (res.ok) setMessages(await res.json());
  };

  const handleSendMessage = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newMessage.trim()) return;

    const payload = {
      sender_id: user?.id,
      content: newMessage,
      receiver_id: activeTab === 'personal' ? selectedLecturer?.id : null,
      class_id: activeTab === 'group' ? selectedClass?.id : null,
      group_id: activeTab === 'group' && studentGroup ? studentGroup.id : null
    };
    
    const res = await fetch('/api/chat/send', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    if (res.ok) {
      setNewMessage('');
      if (activeTab === 'personal') fetchPersonalMessages();
      else fetchGroupMessages();
    }
  };

  const [isSidebarOpen, setIsSidebarOpen] = useState(true);

  useEffect(() => {
    const handleResize = () => {
      if (window.innerWidth < 768) {
        setIsSidebarOpen(false);
      } else {
        setIsSidebarOpen(true);
      }
    };
    handleResize();
    window.addEventListener('resize', handleResize);
    return () => window.removeEventListener('resize', handleResize);
  }, []);

  const toggleSidebar = () => setIsSidebarOpen(!isSidebarOpen);

  const selectLecturer = (lect: Conversation) => {
    setSelectedLecturer(lect);
    if (window.innerWidth < 768) setIsSidebarOpen(false);
  };

  const selectClass = (cls: ClassGroup) => {
    setSelectedClass(cls);
    if (window.innerWidth < 768) setIsSidebarOpen(false);
  };

  return (
    <div className="h-[calc(100vh-100px)] flex flex-col relative">
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-2xl font-bold text-gray-800 flex items-center gap-2">
          <MessageCircle /> Pusat Pesan
        </h1>
        <button 
          onClick={toggleSidebar}
          className="md:hidden p-2 bg-white rounded-lg border border-gray-200 shadow-sm text-gray-600"
        >
          <Users size={20} />
        </button>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-200 flex-1 flex overflow-hidden relative">
        {/* Sidebar List */}
        <div className={`
          ${isSidebarOpen ? 'translate-x-0' : '-translate-x-full'}
          md:translate-x-0 transition-transform duration-300 ease-in-out
          absolute md:static inset-y-0 left-0 z-20 w-full md:w-1/3 bg-white border-r border-gray-200 flex flex-col
        `}>
          <div className="flex border-b border-gray-200">
            <button
              onClick={() => { setActiveTab('group'); setSelectedClass(null); }}
              className={`flex-1 py-3 text-sm font-medium flex justify-center items-center gap-2 ${activeTab === 'group' ? 'bg-indigo-50 text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-600 hover:bg-gray-50'}`}
            >
              <Users size={16} /> Grup Kelas
            </button>
            <button
              onClick={() => { setActiveTab('personal'); setSelectedLecturer(null); }}
              className={`flex-1 py-3 text-sm font-medium flex justify-center items-center gap-2 ${activeTab === 'personal' ? 'bg-indigo-50 text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-600 hover:bg-gray-50'}`}
            >
              <User size={16} /> Dosen
            </button>
          </div>

          <div className="flex-1 overflow-y-auto">
            {activeTab === 'personal' ? (
              lecturers.length === 0 ? (
                <p className="text-center text-gray-500 p-4 text-sm">Belum ada dosen.</p>
              ) : (
                lecturers.map(lect => (
                  <button
                    key={lect.id}
                    onClick={() => selectLecturer(lect)}
                    className={`w-full p-4 text-left border-b hover:bg-gray-50 transition ${selectedLecturer?.id === lect.id ? 'bg-indigo-50' : ''}`}
                  >
                    <div className="font-semibold text-gray-800">{lect.name}</div>
                    <div className="text-xs text-gray-500 mb-1">Dosen Pengampu</div>
                  </button>
                ))
              )
            ) : (
              classes.map(cls => (
                <button
                  key={cls.id}
                  onClick={() => selectClass(cls)}
                  className={`w-full p-4 text-left border-b hover:bg-gray-50 transition ${selectedClass?.id === cls.id ? 'bg-indigo-50' : ''}`}
                >
                  <div className="font-semibold text-gray-800">{cls.name}</div>
                  <div className="text-xs text-gray-500">{cls.course_name}</div>
                </button>
              ))
            )}
          </div>
        </div>

        {/* Chat Area */}
        <div className="flex-1 flex flex-col bg-gray-50 w-full">
          {(activeTab === 'personal' && !selectedLecturer) || (activeTab === 'group' && !selectedClass) ? (
            <div className="flex-1 flex items-center justify-center text-gray-400">
              <div className="text-center">
                <MessageCircle size={48} className="mx-auto mb-2 opacity-50" />
                <p>Pilih percakapan untuk memulai</p>
              </div>
            </div>
          ) : (
            <>
              {/* Chat Header */}
              <div className="bg-white p-4 border-b border-gray-200 flex justify-between items-center shadow-sm">
                <div>
                  <h3 className="font-bold text-gray-800">
                    {activeTab === 'personal' ? selectedLecturer?.name : (studentGroup ? `${selectedClass?.name} - ${studentGroup.name}` : selectedClass?.name)}
                  </h3>
                  <p className="text-xs text-gray-500">
                    {activeTab === 'personal' ? 'Dosen' : (studentGroup ? 'Diskusi Kelompok' : 'Diskusi Kelas')}
                  </p>
                </div>
              </div>

              {/* Messages */}
              <div className="flex-1 overflow-y-auto p-4 space-y-4">
                {messages.map((msg, idx) => {
                  const isMe = msg.sender_id === user?.id;
                  return (
                    <div key={idx} className={`flex ${isMe ? 'justify-end' : 'justify-start'}`}>
                      <div className={`max-w-[70%] rounded-2xl p-3 shadow-sm ${isMe ? 'bg-indigo-600 text-white rounded-br-none' : 'bg-white text-gray-800 rounded-bl-none border border-gray-200'}`}>
                        {!isMe && activeTab === 'group' && (
                          <div className="text-xs font-bold text-indigo-600 mb-1">{msg.sender_name}</div>
                        )}
                        <p className="text-sm">{msg.content}</p>
                        <div className={`text-[10px] mt-1 text-right ${isMe ? 'text-indigo-200' : 'text-gray-400'}`}>
                          {new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                        </div>
                      </div>
                    </div>
                  );
                })}
                <div ref={messagesEndRef} />
              </div>

              {/* Input */}
              <form onSubmit={handleSendMessage} className="p-4 bg-white border-t border-gray-200">
                <div className="flex gap-2">
                  <input
                    value={newMessage}
                    onChange={e => setNewMessage(e.target.value)}
                    placeholder="Tulis pesan..."
                    className="flex-1 border border-gray-300 rounded-full px-4 py-2 focus:outline-none focus:border-indigo-500"
                  />
                  <button 
                    type="submit" 
                    disabled={!newMessage.trim()}
                    className="bg-indigo-600 text-white p-2 rounded-full hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                  >
                    <Send size={20} />
                  </button>
                </div>
              </form>
            </>
          )}
        </div>
      </div>
    </div>
  );
}
