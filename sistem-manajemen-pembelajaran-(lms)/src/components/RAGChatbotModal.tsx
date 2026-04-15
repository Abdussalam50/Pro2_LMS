import React, { useState, useEffect, useRef } from 'react';
import { X, Send, Bot, User, Sparkles, MessageSquare, BookOpen } from 'lucide-react';
import { GoogleGenAI } from "@google/genai";
import { useAuth } from '../context/AuthContext';

interface Message {
  role: 'user' | 'model';
  text: string;
}

interface Reference {
  title: string;
  content: string;
}

interface RAGChatbotModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export default function RAGChatbotModal({ isOpen, onClose }: RAGChatbotModalProps) {
  const { user } = useAuth();
  const [messages, setMessages] = useState<Message[]>([
    { role: 'model', text: 'Halo! Saya adalah Asisten AI Akademik. Saya bisa membantu Anda menjawab pertanyaan berdasarkan basis pengetahuan RAG yang telah diinput oleh dosen. Apa yang ingin Anda tanyakan?' }
  ]);
  const [input, setInput] = useState('');
  const [loading, setLoading] = useState(false);
  const [references, setReferences] = useState<Reference[]>([]);
  const messagesEndRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (isOpen) {
      fetchReferences();
    }
  }, [isOpen]);

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
  };

  const fetchReferences = async () => {
    try {
      const res = await fetch('/api/rag-documents');
      if (res.ok) {
        setReferences(await res.json());
      }
    } catch (e) {
      console.error(e);
    }
  };

  const handleSend = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!input.trim() || loading) return;

    const userMessage = input.trim();
    setInput('');
    setMessages(prev => [...prev, { role: 'user', text: userMessage }]);
    setLoading(true);

    try {
      const ai = new GoogleGenAI({ apiKey: process.env.GEMINI_API_KEY });
      
      // Construct context from references
      const referenceContext = references.map(ref => 
        `DOKUMEN: ${ref.title}
         ISI: ${ref.content}`
      ).join('\n\n---\n\n');

      const systemInstruction = `
        Anda adalah Asisten AI Akademik untuk sistem LMS Pro.
        Tugas Anda adalah membantu pengguna (mahasiswa/dosen) memahami materi berdasarkan basis pengetahuan RAG berikut:
        
        BASIS PENGETAHUAN TERSEDIA:
        ${referenceContext}
        
        INSTRUKSI:
        1. Jawablah pertanyaan pengguna dengan sopan dan profesional.
        2. Gunakan informasi dari basis pengetahuan di atas untuk menjawab.
        3. Jika pertanyaan tidak ada di basis pengetahuan, Anda boleh menjawab menggunakan pengetahuan umum Anda tetapi tetap kaitkan dengan konteks akademik.
        4. Gunakan Bahasa Indonesia yang baik dan benar.
      `;

      const response = await ai.models.generateContent({
        model: "gemini-3-flash-preview",
        contents: [
          { role: 'user', parts: [{ text: userMessage }] }
        ],
        config: {
          systemInstruction: systemInstruction,
        }
      });

      const aiText = response.text || "Maaf, saya mengalami kendala teknis. Bisa ulangi pertanyaannya?";
      setMessages(prev => [...prev, { role: 'model', text: aiText }]);
    } catch (error) {
      console.error("Gemini Error:", error);
      setMessages(prev => [...prev, { role: 'model', text: "Maaf, layanan AI sedang tidak tersedia saat ini." }]);
    } finally {
      setLoading(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-[70] flex items-center justify-center p-4 animate-in fade-in duration-200">
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-2xl h-[80vh] flex flex-col overflow-hidden border border-gray-100">
        {/* Header */}
        <div className="p-4 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-indigo-600 to-violet-600 text-white">
          <div className="flex items-center gap-3">
            <div className="p-2 bg-white/20 rounded-xl">
              <Bot size={24} />
            </div>
            <div>
              <h2 className="text-lg font-bold flex items-center gap-2">
                AI Assistant
                <span className="bg-white/20 text-[10px] px-1.5 py-0.5 rounded-full font-normal uppercase tracking-wider">Beta</span>
              </h2>
              <p className="text-indigo-100 text-[10px]">Tanya jawab cerdas berbasis referensi</p>
            </div>
          </div>
          <button 
            onClick={onClose}
            className="p-2 hover:bg-white/20 rounded-full transition-colors"
          >
            <X size={20} />
          </button>
        </div>

        {/* Chat Area */}
        <div className="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50/50">
          {messages.map((msg, idx) => (
            <div key={idx} className={`flex ${msg.role === 'user' ? 'justify-end' : 'justify-start'}`}>
              <div className={`flex gap-3 max-w-[85%] ${msg.role === 'user' ? 'flex-row-reverse' : ''}`}>
                <div className={`w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm ${
                  msg.role === 'user' ? 'bg-indigo-600 text-white' : 'bg-white text-indigo-600 border border-indigo-100'
                }`}>
                  {msg.role === 'user' ? <User size={16} /> : <Bot size={16} />}
                </div>
                <div className={`p-3 rounded-2xl shadow-sm text-sm leading-relaxed ${
                  msg.role === 'user' 
                    ? 'bg-indigo-600 text-white rounded-tr-none' 
                    : 'bg-white text-gray-800 rounded-tl-none border border-gray-100'
                }`}>
                  {msg.text}
                </div>
              </div>
            </div>
          ))}
          {loading && (
            <div className="flex justify-start">
              <div className="flex gap-3 max-w-[85%]">
                <div className="w-8 h-8 rounded-full bg-white text-indigo-600 border border-indigo-100 flex items-center justify-center flex-shrink-0 shadow-sm">
                  <Bot size={16} />
                </div>
                <div className="bg-white p-3 rounded-2xl rounded-tl-none border border-gray-100 shadow-sm flex gap-1">
                  <div className="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce"></div>
                  <div className="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce [animation-delay:0.2s]"></div>
                  <div className="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce [animation-delay:0.4s]"></div>
                </div>
              </div>
            </div>
          )}
          <div ref={messagesEndRef} />
        </div>

        {/* Reference Context Indicator */}
        <div className="px-4 py-2 bg-indigo-50 border-t border-indigo-100 flex items-center gap-2 overflow-x-auto no-scrollbar">
          <Sparkles size={14} className="text-indigo-600 flex-shrink-0" />
          <span className="text-[10px] font-bold text-indigo-700 uppercase whitespace-nowrap">Konteks:</span>
          <div className="flex gap-2">
            {references.slice(0, 3).map((ref, i) => (
              <span key={i} className="text-[10px] bg-white text-indigo-600 px-2 py-0.5 rounded-full border border-indigo-200 whitespace-nowrap">
                {ref.title}
              </span>
            ))}
            {references.length > 3 && <span className="text-[10px] text-indigo-400">+{references.length - 3} lainnya</span>}
          </div>
        </div>

        {/* Input Area */}
        <form onSubmit={handleSend} className="p-4 bg-white border-t border-gray-100 flex gap-2">
          <input 
            value={input}
            onChange={e => setInput(e.target.value)}
            placeholder="Tanyakan sesuatu tentang referensi..."
            className="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all"
          />
          <button 
            type="submit"
            disabled={!input.trim() || loading}
            className="bg-indigo-600 text-white p-2.5 rounded-xl hover:bg-indigo-700 disabled:opacity-50 transition-all shadow-md shadow-indigo-100 active:scale-95"
          >
            <Send size={20} />
          </button>
        </form>
      </div>
    </div>
  );
}
