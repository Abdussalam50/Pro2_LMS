import React, { useState, useEffect } from 'react';
import { X, Plus, FileText, Trash2, ExternalLink, Search, Book } from 'lucide-react';
import { useAuth } from '../context/AuthContext';

interface Reference {
  id: number;
  title: string;
  author: string;
  field: string;
  publisher: string;
  file_url: string;
  lecturer_name: string;
  created_at: string;
}

interface ReferenceModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export default function ReferenceModal({ isOpen, onClose }: ReferenceModalProps) {
  const { user } = useAuth();
  const [references, setReferences] = useState<Reference[]>([]);
  const [loading, setLoading] = useState(true);
  const [showAddForm, setShowAddForm] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  
  const [newRef, setNewRef] = useState({
    title: '',
    author: '',
    field: '',
    publisher: '',
    file_url: ''
  });

  useEffect(() => {
    if (isOpen) {
      fetchReferences();
    }
  }, [isOpen]);

  const fetchReferences = async () => {
    setLoading(true);
    try {
      const res = await fetch('/api/references');
      if (res.ok) {
        setReferences(await res.json());
      }
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  const handleAddReference = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const res = await fetch('/api/references', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          ...newRef,
          lecturer_id: user?.id
        })
      });

      if (res.ok) {
        setNewRef({ title: '', author: '', field: '', publisher: '', file_url: '' });
        setShowAddForm(false);
        fetchReferences();
      } else {
        alert('Gagal menambahkan referensi');
      }
    } catch (e) {
      console.error(e);
      alert('Terjadi kesalahan');
    }
  };

  const handleDeleteReference = async (id: number) => {
    if (!confirm('Hapus referensi ini?')) return;
    try {
      const res = await fetch(`/api/references/${id}`, { method: 'DELETE' });
      if (res.ok) {
        fetchReferences();
      }
    } catch (e) {
      console.error(e);
    }
  };

  const filteredReferences = references.filter(ref => 
    ref.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
    ref.author.toLowerCase().includes(searchTerm.toLowerCase()) ||
    ref.field.toLowerCase().includes(searchTerm.toLowerCase())
  );

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4 animate-in fade-in duration-200">
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden border border-gray-100">
        {/* Header */}
        <div className="p-6 border-b border-gray-100 flex justify-between items-center bg-indigo-600 text-white">
          <div className="flex items-center gap-3">
            <div className="p-2 bg-white/20 rounded-lg">
              <Book size={24} />
            </div>
            <div>
              <h2 className="text-xl font-bold">Referensi Eksternal</h2>
              <p className="text-indigo-100 text-xs">Kumpulan materi dan rujukan pembelajaran</p>
            </div>
          </div>
          <button 
            onClick={onClose}
            className="p-2 hover:bg-white/20 rounded-full transition-colors"
          >
            <X size={24} />
          </button>
        </div>

        {/* Content */}
        <div className="flex-1 overflow-y-auto p-6 bg-gray-50/50">
          {user?.role === 'dosen' && (
            <div className="mb-8">
              {!showAddForm ? (
                <button 
                  onClick={() => setShowAddForm(true)}
                  className="w-full py-4 border-2 border-dashed border-indigo-200 rounded-xl text-indigo-600 font-medium hover:bg-indigo-50 hover:border-indigo-300 transition-all flex items-center justify-center gap-2 group"
                >
                  <Plus size={20} className="group-hover:scale-110 transition-transform" />
                  Tambah Referensi Baru
                </button>
              ) : (
                <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-200 animate-in slide-in-from-top-4 duration-300">
                  <div className="flex justify-between items-center mb-4">
                    <h3 className="font-bold text-gray-800">Input Referensi Baru</h3>
                    <button onClick={() => setShowAddForm(false)} className="text-gray-400 hover:text-gray-600">
                      <X size={20} />
                    </button>
                  </div>
                  <form onSubmit={handleAddReference} className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-1">
                      <label className="text-xs font-bold text-gray-500 uppercase">Nama Referensi</label>
                      <input 
                        required
                        value={newRef.title}
                        onChange={e => setNewRef({...newRef, title: e.target.value})}
                        className="w-full p-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all"
                        placeholder="Contoh: Dasar-dasar Pemrograman"
                      />
                    </div>
                    <div className="space-y-1">
                      <label className="text-xs font-bold text-gray-500 uppercase">Author / Penulis</label>
                      <input 
                        required
                        value={newRef.author}
                        onChange={e => setNewRef({...newRef, author: e.target.value})}
                        className="w-full p-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all"
                        placeholder="Nama Penulis"
                      />
                    </div>
                    <div className="space-y-1">
                      <label className="text-xs font-bold text-gray-500 uppercase">Bidang / Kategori</label>
                      <input 
                        required
                        value={newRef.field}
                        onChange={e => setNewRef({...newRef, field: e.target.value})}
                        className="w-full p-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all"
                        placeholder="Contoh: Teknologi Informasi"
                      />
                    </div>
                    <div className="space-y-1">
                      <label className="text-xs font-bold text-gray-500 uppercase">Penerbit</label>
                      <input 
                        required
                        value={newRef.publisher}
                        onChange={e => setNewRef({...newRef, publisher: e.target.value})}
                        className="w-full p-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all"
                        placeholder="Nama Penerbit"
                      />
                    </div>
                    <div className="space-y-1 md:col-span-2">
                      <label className="text-xs font-bold text-gray-500 uppercase">URL File / Link Referensi</label>
                      <input 
                        required
                        value={newRef.file_url}
                        onChange={e => setNewRef({...newRef, file_url: e.target.value})}
                        className="w-full p-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all"
                        placeholder="https://example.com/file.pdf"
                      />
                    </div>
                    <div className="md:col-span-2 flex justify-end gap-3 mt-2">
                      <button 
                        type="button"
                        onClick={() => setShowAddForm(false)}
                        className="px-4 py-2 text-gray-600 font-medium hover:bg-gray-100 rounded-lg transition-colors"
                      >
                        Batal
                      </button>
                      <button 
                        type="submit"
                        className="px-6 py-2 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 shadow-md shadow-indigo-200 transition-all active:scale-95"
                      >
                        Simpan Referensi
                      </button>
                    </div>
                  </form>
                </div>
              )}
            </div>
          )}

          {/* Search and List */}
          <div className="space-y-6">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" size={18} />
              <input 
                type="text"
                placeholder="Cari referensi berdasarkan judul, penulis, atau bidang..."
                value={searchTerm}
                onChange={e => setSearchTerm(e.target.value)}
                className="w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none shadow-sm transition-all"
              />
            </div>

            {loading ? (
              <div className="flex flex-col items-center justify-center py-20 gap-3">
                <div className="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                <p className="text-gray-500 font-medium">Memuat referensi...</p>
              </div>
            ) : filteredReferences.length === 0 ? (
              <div className="text-center py-20 bg-white rounded-2xl border border-dashed border-gray-200">
                <div className="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                  <Book size={32} className="text-gray-300" />
                </div>
                <h3 className="text-gray-800 font-bold">Tidak ada referensi ditemukan</h3>
                <p className="text-gray-500 text-sm">Coba gunakan kata kunci pencarian lain</p>
              </div>
            ) : (
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {filteredReferences.map((ref) => (
                  <div key={ref.id} className="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-indigo-100 transition-all group relative overflow-hidden">
                    <div className="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-full -mr-12 -mt-12 group-hover:bg-indigo-100 transition-colors"></div>
                    
                    <div className="relative">
                      <div className="flex justify-between items-start mb-3">
                        <div className="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                          <FileText size={20} />
                        </div>
                        {user?.role === 'dosen' && (
                          <button 
                            onClick={() => handleDeleteReference(ref.id)}
                            className="p-2 text-gray-300 hover:text-red-500 transition-colors"
                          >
                            <Trash2 size={18} />
                          </button>
                        )}
                      </div>
                      
                      <h4 className="font-bold text-gray-800 text-lg mb-1 line-clamp-2 leading-tight">{ref.title}</h4>
                      <p className="text-indigo-600 text-sm font-medium mb-4">{ref.author}</p>
                      
                      <div className="space-y-2 mb-5">
                        <div className="flex items-center gap-2 text-xs text-gray-500">
                          <span className="font-bold uppercase tracking-wider w-20">Bidang:</span>
                          <span className="bg-gray-100 px-2 py-0.5 rounded text-gray-700">{ref.field}</span>
                        </div>
                        <div className="flex items-center gap-2 text-xs text-gray-500">
                          <span className="font-bold uppercase tracking-wider w-20">Penerbit:</span>
                          <span className="text-gray-700">{ref.publisher}</span>
                        </div>
                        <div className="flex items-center gap-2 text-[10px] text-gray-400 italic">
                          <span>Oleh: {ref.lecturer_name}</span>
                          <span>•</span>
                          <span>{new Date(ref.created_at).toLocaleDateString('id-ID')}</span>
                        </div>
                      </div>

                      <a 
                        href={ref.file_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="w-full py-2.5 bg-gray-50 text-gray-700 font-bold rounded-xl hover:bg-indigo-600 hover:text-white transition-all flex items-center justify-center gap-2 border border-gray-100"
                      >
                        <ExternalLink size={16} />
                        Akses Referensi
                      </a>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
