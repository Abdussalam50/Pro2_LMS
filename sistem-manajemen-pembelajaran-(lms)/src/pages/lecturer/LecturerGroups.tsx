import React, { useEffect, useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import { Users, Plus, Trash2, UserPlus, X, ChevronRight } from 'lucide-react';

interface Class {
  id: number;
  name: string;
  code: string;
  course_name: string;
}

interface Student {
  id: number;
  name: string;
  username: string;
}

interface Group {
  id: number;
  name: string;
  members: Student[];
}

export default function LecturerGroups() {
  const { user } = useAuth();
  const [classes, setClasses] = useState<Class[]>([]);
  const [selectedClassId, setSelectedClassId] = useState<number | null>(null);
  
  const [groups, setGroups] = useState<Group[]>([]);
  const [students, setStudents] = useState<Student[]>([]);
  const [loading, setLoading] = useState(false);

  const [newGroupName, setNewGroupName] = useState('');
  const [showAddMemberModal, setShowAddMemberModal] = useState<number | null>(null); // group_id

  useEffect(() => {
    if (user) fetchClasses();
  }, [user]);

  useEffect(() => {
    if (selectedClassId) {
      fetchGroups();
      fetchStudents();
    }
  }, [selectedClassId]);

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

  const fetchGroups = async () => {
    setLoading(true);
    const res = await fetch(`/api/classes/${selectedClassId}/groups`);
    if (res.ok) setGroups(await res.json());
    setLoading(false);
  };

  const fetchStudents = async () => {
    const res = await fetch(`/api/classes/${selectedClassId}/students`);
    if (res.ok) setStudents(await res.json());
  };

  const handleCreateGroup = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newGroupName.trim()) return;

    const res = await fetch('/api/groups', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ class_id: selectedClassId, name: newGroupName })
    });

    if (res.ok) {
      setNewGroupName('');
      fetchGroups();
    }
  };

  const handleDeleteGroup = async (id: number) => {
    if (!confirm('Hapus kelompok ini?')) return;
    await fetch(`/api/groups/${id}`, { method: 'DELETE' });
    fetchGroups();
  };

  const handleAddMember = async (groupId: number, studentId: number) => {
    const res = await fetch(`/api/groups/${groupId}/members`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ student_id: studentId })
    });
    
    if (res.ok) {
      fetchGroups();
      // Don't close modal immediately to allow adding multiple
    } else {
      alert('Mahasiswa sudah ada di kelompok ini');
    }
  };

  const handleRemoveMember = async (groupId: number, studentId: number) => {
    await fetch(`/api/groups/${groupId}/members/${studentId}`, { method: 'DELETE' });
    fetchGroups();
  };

  if (!selectedClassId) {
    return (
      <div className="pb-20">
        <h1 className="text-2xl font-bold text-gray-800 mb-2">Manajemen Kelompok</h1>
        <p className="text-gray-500 mb-8">Pilih kelas untuk mengatur kelompok belajar mahasiswa.</p>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {classes.map(cls => (
            <button
              key={cls.id}
              onClick={() => setSelectedClassId(cls.id)}
              className="bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition text-left group relative overflow-hidden"
            >
              <div className="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-bl-full -mr-4 -mt-4 transition group-hover:bg-indigo-100"></div>
              
              <div className="relative z-10">
                <div className="flex justify-between items-start mb-4">
                  <div className="bg-indigo-100 p-3 rounded-lg text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition">
                    <Users size={24} />
                  </div>
                  <span className="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded font-mono font-bold">{cls.code}</span>
                </div>
                <h3 className="text-xl font-bold text-gray-800 mb-1">{cls.name}</h3>
                <p className="text-sm text-gray-500 mb-4">{cls.course_name}</p>
                <div className="flex items-center text-indigo-600 text-sm font-medium">
                  Atur Kelompok <ChevronRight size={16} className="ml-1" />
                </div>
              </div>
            </button>
          ))}
        </div>
      </div>
    );
  }

  const selectedClass = classes.find(c => c.id === selectedClassId);
  const activeGroup = groups.find(g => g.id === showAddMemberModal);

  return (
    <div className="pb-20 max-w-6xl mx-auto relative">
      <button 
        onClick={() => setSelectedClassId(null)}
        className="text-gray-500 hover:text-indigo-600 mb-6 flex items-center text-sm font-medium transition"
      >
        &larr; Kembali ke Daftar Kelas
      </button>

      <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
          <h1 className="text-3xl font-bold text-gray-800">{selectedClass?.name}</h1>
          <p className="text-gray-500 mt-1">Manajemen Kelompok Belajar</p>
        </div>
        
        <form onSubmit={handleCreateGroup} className="flex gap-2 w-full md:w-auto">
          <input
            value={newGroupName}
            onChange={e => setNewGroupName(e.target.value)}
            placeholder="Nama Kelompok Baru..."
            className="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-indigo-500 flex-1 md:w-64"
          />
          <button type="submit" className="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium flex items-center gap-2 shadow-sm">
            <Plus size={20} /> <span className="hidden sm:inline">Buat</span>
          </button>
        </form>
      </div>

      {loading ? (
        <div className="text-center py-12">Loading...</div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
          {groups.map(group => (
            <div key={group.id} className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
              <div className="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 className="font-bold text-gray-800 text-lg">{group.name}</h3>
                <button onClick={() => handleDeleteGroup(group.id)} className="text-gray-400 hover:text-red-500 transition p-1 rounded hover:bg-red-50">
                  <Trash2 size={18} />
                </button>
              </div>
              
              <div className="p-4 flex-1">
                {group.members.length === 0 ? (
                  <p className="text-sm text-gray-400 italic text-center py-4">Belum ada anggota.</p>
                ) : (
                  <ul className="space-y-2">
                    {group.members.map(member => (
                      <li key={member.id} className="flex justify-between items-center text-sm bg-white border border-gray-100 p-2 rounded hover:border-gray-300 transition group/item">
                        <div className="flex items-center gap-2">
                          <div className="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">
                            {member.name.charAt(0)}
                          </div>
                          <span className="text-gray-700">{member.name}</span>
                        </div>
                        <button 
                          onClick={() => handleRemoveMember(group.id, member.id)}
                          className="text-gray-300 hover:text-red-500 opacity-0 group-hover/item:opacity-100 transition"
                        >
                          <X size={14} />
                        </button>
                      </li>
                    ))}
                  </ul>
                )}
              </div>

              <div className="p-3 bg-gray-50 border-t border-gray-100">
                <button 
                  onClick={() => setShowAddMemberModal(group.id)}
                  className="w-full py-2 text-sm text-indigo-600 font-medium hover:bg-indigo-50 rounded border border-dashed border-indigo-200 hover:border-indigo-300 transition flex items-center justify-center gap-2"
                >
                  <UserPlus size={16} /> Tambah Anggota
                </button>
              </div>
            </div>
          ))}
          
          {groups.length === 0 && (
            <div className="col-span-full text-center py-12 bg-white rounded-xl border border-dashed border-gray-300">
              <Users className="mx-auto text-gray-300 mb-4" size={48} />
              <p className="text-gray-500 font-medium">Belum ada kelompok yang dibuat.</p>
              <p className="text-gray-400 text-sm">Buat kelompok baru untuk memulai.</p>
            </div>
          )}
        </div>
      )}

      {/* Add Member Modal */}
      {showAddMemberModal && activeGroup && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-xl max-w-md w-full overflow-hidden flex flex-col max-h-[80vh]">
            <div className="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
              <h3 className="font-bold text-gray-800">Tambah Anggota ke {activeGroup.name}</h3>
              <button onClick={() => setShowAddMemberModal(null)} className="text-gray-400 hover:text-gray-600">
                <X size={20} />
              </button>
            </div>
            
            <div className="p-4 overflow-y-auto flex-1">
              <div className="space-y-2">
                {students
                  .filter(s => !activeGroup.members.find(m => m.id === s.id))
                  .length === 0 ? (
                    <p className="text-center text-gray-500 py-4">Semua mahasiswa sudah masuk kelompok ini.</p>
                  ) : (
                    students
                      .filter(s => !activeGroup.members.find(m => m.id === s.id))
                      .map(s => (
                        <div key={s.id} className="flex justify-between items-center p-3 border rounded-lg hover:bg-gray-50">
                          <div>
                            <div className="font-medium text-gray-800">{s.name}</div>
                            <div className="text-xs text-gray-500">{s.username}</div>
                          </div>
                          <button
                            onClick={() => handleAddMember(activeGroup.id, s.id)}
                            className="bg-indigo-100 text-indigo-700 p-2 rounded-full hover:bg-indigo-200 transition"
                          >
                            <Plus size={16} />
                          </button>
                        </div>
                      ))
                  )
                }
              </div>
            </div>
            
            <div className="p-4 border-t border-gray-200 bg-gray-50 text-right">
              <button 
                onClick={() => setShowAddMemberModal(null)}
                className="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium text-sm"
              >
                Selesai
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
