import React, { useState, useEffect } from 'react';
import { X, Plus, Trash2, Search, Database, FileText, Sparkles } from 'lucide-react';
import { useAuth } from '../context/AuthContext';

interface RAGDocument {
  id: number;
  title: string;
  content: string;
  lecturer_name: string;
  created_at: string;
}

interface RAGDocumentModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export default function RAGDocumentModal({ isOpen, onClose }: RAGDocumentModalProps) {
  const { user } = useAuth();
  const [documents, setDocuments] = useState<RAGDocument[]>([]);
  const [loading, setLoading] = useState(true);
  const [showAddForm, setShowAddForm] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  
  const [newDoc, setNewDoc] = useState({
    title: '',
    content: ''
  });

  useEffect(() => {
    if (isOpen) {
      fetchDocuments();
    }
  }, [isOpen]);

  const fetchDocuments = async () => {
    setLoading(true);
    try {
      const res = await fetch('/api/rag-documents');
      if (res.ok) {
        setDocuments(await res.json());
      }
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  const handleAddDocument = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const res = await fetch('/api/rag-documents', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          ...newDoc,
          lecturer_id: user?.id
        })
      });

      if (res.ok) {
        setNewDoc({ title: '', content: '' });
        setShowAddForm(false);
        fetchDocuments();
      }
    } catch (e) {
      console.error(e);
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Hapus dokumen pengetahuan ini?')) return;
    try {
      const res = await fetch(`/api/rag-documents/${id}`, { method: 'DELETE' });
      if (res.ok) {
        fetchDocuments();
      }
    } catch (e) {
      console.error(e);
    }
  };

  if (!isOpen) return null;

  const filteredDocs = documents.filter(doc => 
    doc.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
    doc.content.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-[70] flex items-center justify-center p-4 animate-in fade-in duration-200">
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden border border-gray-100">
        {/* Header */}
        <div className="p-6 border-b border-gray-100 flex justify-between items-center bg-indigo-700 text-white">
          <div className="flex items-center gap-3">
            <div className="p-2 bg-white/20 rounded-lg">
              <Database size={24} />
            </div>
            <div>
              <h2 className="text-xl font-bold">Input Dokumen RAG</h2>
              <p className="text-indigo-100 text-xs">Kelola basis pengetahuan untuk asisten AI</p>
            </div>
          </div>
          <button 
            onClick={onClose}
            className="p-2 hover:bg-white/20 rounded-full transition-colors"
          >
            <X size={20} />
          </button>
        </div>

        {/* Content */}
        <div className="flex-1 overflow-y-auto p-6 bg-gray-50/30">
          <div className="max-w-3xl mx-auto space-y-6">
            {/* Add Form Section (Lecturer Only) */}
            {user?.role === 'dosen' && (
              <div className="space-y-4">
                {!showAddForm ? (
                  <button 
                    onClick={() => setShowAddForm(true)}
                    className="w-full py-4 border-2 border-dashed border-indigo-200 rounded-xl text-indigo-700 font-bold hover:bg-indigo-50 hover:border-indigo-300 transition-all flex items-center justify-center gap-2 group"
                  >
                    <Plus size={20} className="group-hover:scale-110 transition-transform" />
                    Tambah Dokumen Pengetahuan Baru
                  </button>
                ) : (
                  <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-200 animate-in slide-in-from-top-4 duration-300">
                    <div className="flex justify-between items-center mb-4">
                      <h3 className="font-bold text-gray-800 flex items-center gap-2">
                        <Sparkles size={18} className="text-indigo-600" />
                        Input Pengetahuan Baru
                      </h3>
                      <button onClick={() => setShowAddForm(false)} className="text-gray-400 hover:text-gray-600">
                        <X size={20} />
                      </button>
                    </div>
                    <form onSubmit={handleAddDocument} className="space-y-4">
                      <div className="space-y-1">
                        <label className="text-xs font-bold text-gray-500 uppercase tracking-wider">Judul Dokumen</label>
                        <input 
                          required
                          value={newDoc.title}
                          onChange={e => setNewDoc({...newDoc, title: e.target.value})}
                          className="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all text-sm"
                          placeholder="Contoh: Ringkasan Materi Pertemuan 1"
                        />
                      </div>
                      <div className="space-y-1">
                        <label className="text-xs font-bold text-gray-500 uppercase tracking-wider">Isi Pengetahuan (Teks RAG)</label>
                        <textarea 
                          required
                          value={newDoc.content}
                          onChange={e => setNewDoc({...newDoc, content: e.target.value})}
                          className="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all h-48 text-sm leading-relaxed"
                          placeholder="Masukkan teks materi yang ingin dipelajari oleh AI..."
                        />
                        <p className="text-[10px] text-gray-400 italic">Teks ini akan diproses oleh AI untuk menjawab pertanyaan mahasiswa secara cerdas.</p>
                      </div>
                      <div className="flex justify-end gap-3 pt-2">
                        <button 
                          type="button"
                          onClick={() => setShowAddForm(false)}
                          className="px-4 py-2 text-gray-600 font-medium hover:bg-gray-100 rounded-lg transition-colors"
                        >
                          Batal
                        </button>
                        <button 
                          type="submit"
                          className="px-6 py-2 bg-indigo-700 text-white font-bold rounded-lg hover:bg-indigo-800 shadow-lg shadow-indigo-200 transition-all active:scale-95"
                        >
                          Simpan ke Basis Pengetahuan
                        </button>
                      </div>
                    </form>
                  </div>
                )}
              </div>
            )}

            {/* Search and List */}
            <div className="space-y-4">
              <div className="relative">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" size={18} />
                <input 
                  type="text"
                  placeholder="Cari dokumen pengetahuan..."
                  value={searchTerm}
                  onChange={e => setSearchTerm(e.target.value)}
                  className="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all shadow-sm"
                />
              </div>

              {loading ? (
                <div className="flex justify-center py-12">
                  <div className="w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                </div>
              ) : filteredDocs.length === 0 ? (
                <div className="text-center py-12 bg-white rounded-xl border border-dashed border-gray-200">
                  <Database size={48} className="mx-auto text-gray-300 mb-3" />
                  <p className="text-gray-500 font-medium">Belum ada dokumen pengetahuan RAG</p>
                  <p className="text-gray-400 text-sm">Input teks materi untuk meningkatkan kecerdasan AI</p>
                </div>
              ) : (
                <div className="grid grid-cols-1 gap-4">
                  {filteredDocs.map(doc => (
                    <div key={doc.id} className="bg-white p-5 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-all group">
                      <div className="flex justify-between items-start mb-3">
                        <div className="flex items-center gap-3">
                          <div className="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                            <FileText size={20} />
                          </div>
                          <div>
                            <h4 className="font-bold text-gray-800">{doc.title}</h4>
                            <p className="text-[10px] text-gray-400 uppercase tracking-wider font-bold">
                              Oleh {doc.lecturer_name} • {new Date(doc.created_at).toLocaleDateString('id-ID')}
                            </p>
                          </div>
                        </div>
                        {user?.role === 'dosen' && (
                          <button 
                            onClick={() => handleDelete(doc.id)}
                            className="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all opacity-0 group-hover:opacity-100"
                          >
                            <Trash2 size={18} />
                          </button>
                        )}
                      </div>
                      <div className="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <p className="text-sm text-gray-600 line-clamp-3 leading-relaxed italic">
                          "{doc.content}"
                        </p>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
