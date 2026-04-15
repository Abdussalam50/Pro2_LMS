import React, { useEffect, useState, useRef } from 'react';
import { useAuth } from '../../context/AuthContext';
import { Send, User, Users, MessageCircle, ChevronDown, ChevronRight, Hash } from 'lucide-react';

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

interface Group {
  id: number;
  name: string;
}

interface Class {
  id: number;
  name: string;
  code: string;
  groups: Group[];
}

interface Course {
  id: number;
  name: string;
  code: string;
  classes: Class[];
}

export default function LecturerChat() {
  const { user } = useAuth();
  const [activeTab, setActiveTab] = useState<'personal' | 'group'>('personal');
  
  // Personal Chat States
  const [conversations, setConversations] = useState<Conversation[]>([]);
  const [selectedUser, setSelectedUser] = useState<Conversation | null>(null);
  
  // Group Chat States
  const [courses, setCourses] = useState<Course[]>([]);
  const [expandedCourses, setExpandedCourses] = useState<number[]>([]);
  const [expandedClasses, setExpandedClasses] = useState<number[]>([]);
  
  const [selectedChat, setSelectedChat] = useState<{
    type: 'class' | 'group';
    classId: number;
    groupId?: number;
    name: string;
    subtitle: string;
  } | null>(null);
  
  // Messages State
  const [messages, setMessages] = useState<Message[]>([]);
  const [newMessage, setNewMessage] = useState('');
  const messagesEndRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (user) {
      if (activeTab === 'personal') fetchConversations();
      else fetchHierarchy();
    }
  }, [user, activeTab]);

  useEffect(() => {
    if (activeTab === 'personal' && selectedUser) {
      fetchPersonalMessages();
      const interval = setInterval(fetchPersonalMessages, 3000);
      return () => clearInterval(interval);
    } else if (activeTab === 'group' && selectedChat) {
      fetchGroupMessages();
      const interval = setInterval(fetchGroupMessages, 3000);
      return () => clearInterval(interval);
    }
  }, [selectedUser, selectedChat, activeTab]);

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
  };

  const fetchConversations = async () => {
    const res = await fetch(`/api/chat/conversations?user_id=${user?.id}`);
    if (res.ok) setConversations(await res.json());
  };

  const fetchHierarchy = async () => {
    const res = await fetch(`/api/lecturer/${user?.id}/chat-hierarchy`);
    if (res.ok) {
      const data = await res.json();
      setCourses(data);
      // Auto expand first course if exists
      if (data.length > 0) setExpandedCourses([data[0].id]);
    }
  };

  const fetchPersonalMessages = async () => {
    if (!selectedUser) return;
    const res = await fetch(`/api/chat/personal/${selectedUser.id}?user_id=${user?.id}`);
    if (res.ok) {
      setMessages(await res.json());
      await fetch('/api/chat/read', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: user?.id, sender_id: selectedUser.id })
      });
    }
  };

  const fetchGroupMessages = async () => {
    if (!selectedChat) return;
    let url = `/api/chat/group/${selectedChat.classId}`;
    if (selectedChat.type === 'group' && selectedChat.groupId) {
      url += `?group_id=${selectedChat.groupId}`;
    }
    const res = await fetch(url);
    if (res.ok) setMessages(await res.json());
  };

  const handleSendMessage = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newMessage.trim()) return;

    const payload: any = {
      sender_id: user?.id,
      content: newMessage,
      group_id: null,
      receiver_id: null,
      class_id: null
    };

    if (activeTab === 'personal') {
      payload.receiver_id = selectedUser?.id;
    } else if (selectedChat) {
      payload.class_id = selectedChat.classId;
      if (selectedChat.type === 'group') {
        payload.group_id = selectedChat.groupId;
      }
    }

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

  const toggleCourse = (id: number) => {
    setExpandedCourses(prev => 
      prev.includes(id) ? prev.filter(c => c !== id) : [...prev, id]
    );
  };

  const toggleClass = (id: number) => {
    setExpandedClasses(prev => 
      prev.includes(id) ? prev.filter(c => c !== id) : [...prev, id]
    );
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

  const selectPersonalUser = (conv: Conversation) => {
    setSelectedUser(conv);
    if (window.innerWidth < 768) setIsSidebarOpen(false);
  };

  const selectGroupChat = (chat: any) => {
    setSelectedChat(chat);
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
              onClick={() => { setActiveTab('personal'); setSelectedUser(null); }}
              className={`flex-1 py-3 text-sm font-medium flex justify-center items-center gap-2 ${activeTab === 'personal' ? 'bg-indigo-50 text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-600 hover:bg-gray-50'}`}
            >
              <User size={16} /> Personal
            </button>
            <button
              onClick={() => { setActiveTab('group'); setSelectedChat(null); }}
              className={`flex-1 py-3 text-sm font-medium flex justify-center items-center gap-2 ${activeTab === 'group' ? 'bg-indigo-50 text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-600 hover:bg-gray-50'}`}
            >
              <Users size={16} /> Kelompok/Kelas
            </button>
          </div>

          <div className="flex-1 overflow-y-auto">
            {activeTab === 'personal' ? (
              conversations.length === 0 ? (
                <p className="text-center text-gray-500 p-4 text-sm">Belum ada percakapan.</p>
              ) : (
                conversations.map(conv => (
                  <button
                    key={conv.id}
                    onClick={() => selectPersonalUser(conv)}
                    className={`w-full p-4 text-left border-b hover:bg-gray-50 transition ${selectedUser?.id === conv.id ? 'bg-indigo-50' : ''}`}
                  >
                    <div className="font-semibold text-gray-800">{conv.name}</div>
                    <div className="text-xs text-gray-500 mb-1">{conv.username}</div>
                    <div className="text-sm text-gray-600 truncate">{conv.last_message}</div>
                    <div className="text-[10px] text-gray-400 mt-1">
                      {new Date(conv.last_message_time).toLocaleString()}
                    </div>
                  </button>
                ))
              )
            ) : (
              <div className="p-2 space-y-1">
                {courses.map(course => (
                  <div key={course.id} className="border rounded-lg overflow-hidden mb-2">
                    <button 
                      onClick={() => toggleCourse(course.id)}
                      className="w-full flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 font-medium text-gray-800"
                    >
                      <span className="text-sm">{course.name}</span>
                      {expandedCourses.includes(course.id) ? <ChevronDown size={16} /> : <ChevronRight size={16} />}
                    </button>
                    
                    {expandedCourses.includes(course.id) && (
                      <div className="bg-white border-t">
                        {course.classes.map(cls => (
                          <div key={cls.id}>
                            <button 
                              onClick={() => toggleClass(cls.id)}
                              className="w-full flex items-center justify-between p-2 pl-6 hover:bg-gray-50 text-sm text-gray-700 border-b border-gray-100"
                            >
                              <span>{cls.name}</span>
                              {expandedClasses.includes(cls.id) ? <ChevronDown size={14} /> : <ChevronRight size={14} />}
                            </button>
    
                            {expandedClasses.includes(cls.id) && (
                              <div className="bg-gray-50 pl-4 py-1">
                                {/* General Class Chat */}
                                <button
                                  onClick={() => selectGroupChat({
                                    type: 'class',
                                    classId: cls.id,
                                    name: `Diskusi Kelas ${cls.name}`,
                                    subtitle: course.name
                                  })}
                                  className={`w-full text-left p-2 pl-6 text-xs flex items-center gap-2 hover:bg-indigo-50 ${selectedChat?.classId === cls.id && selectedChat.type === 'class' ? 'text-indigo-600 font-bold bg-indigo-50' : 'text-gray-600'}`}
                                >
                                  <Hash size={12} /> Diskusi Umum Kelas
                                </button>
    
                                {/* Groups */}
                                {cls.groups.map(grp => (
                                  <button
                                    key={grp.id}
                                    onClick={() => selectGroupChat({
                                      type: 'group',
                                      classId: cls.id,
                                      groupId: grp.id,
                                      name: `Kelompok: ${grp.name}`,
                                      subtitle: `${cls.name} - ${course.name}`
                                    })}
                                    className={`w-full text-left p-2 pl-6 text-xs flex items-center gap-2 hover:bg-indigo-50 ${selectedChat?.groupId === grp.id ? 'text-indigo-600 font-bold bg-indigo-50' : 'text-gray-600'}`}
                                  >
                                    <Users size={12} /> {grp.name}
                                  </button>
                                ))}
                                {cls.groups.length === 0 && (
                                  <div className="pl-8 py-1 text-[10px] text-gray-400 italic">Belum ada kelompok</div>
                                )}
                              </div>
                            )}
                          </div>
                        ))}
                        {course.classes.length === 0 && (
                          <div className="p-3 text-xs text-center text-gray-400">Belum ada kelas</div>
                        )}
                      </div>
                    )}
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>

        {/* Chat Area */}
        <div className="flex-1 flex flex-col bg-gray-50 w-full">
          {(activeTab === 'personal' && !selectedUser) || (activeTab === 'group' && !selectedChat) ? (
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
                    {activeTab === 'personal' ? selectedUser?.name : selectedChat?.name}
                  </h3>
                  <p className="text-xs text-gray-500">
                    {activeTab === 'personal' ? selectedUser?.username : selectedChat?.subtitle}
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
