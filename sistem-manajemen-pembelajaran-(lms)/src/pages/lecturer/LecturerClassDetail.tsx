import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { ArrowLeft, Calendar, Plus, Trash2, FileText, CheckSquare, X, Edit2, Share2, Save, CheckCircle, ExternalLink, Bot, Book, Database } from 'lucide-react';
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

interface Question {
  question_text: string;
  question_type: 'essay' | 'multiple_choice';
  options: string[];
  correct_answer: string;
  points: number;
}

export default function LecturerClassDetail() {
  const { user } = useAuth();
  const { openReferenceModal, openRAGChatbot, openRAGDocumentModal } = useReference();
  const { classId } = useParams();
  const [meetings, setMeetings] = useState<Meeting[]>([]);
  const [materials, setMaterials] = useState<Material[]>([]);
  const [assignments, setAssignments] = useState<Assignment[]>([]);
  const [className, setClassName] = useState('');
  
  // Modals
  const [showMeetingForm, setShowMeetingForm] = useState(false);
  const [showMaterialForm, setShowMaterialForm] = useState<number | null>(null); // meeting_id
  const [showAssignmentForm, setShowAssignmentForm] = useState<number | null>(null); // meeting_id
  const [showShareModal, setShowShareModal] = useState(false);
  
  const [editingMeetingId, setEditingMeetingId] = useState<number | null>(null);
  const [editingMaterialId, setEditingMaterialId] = useState<number | null>(null);
  const [editingAssignmentId, setEditingAssignmentId] = useState<number | null>(null);
  
  // Share State
  const [shareItem, setShareItem] = useState<{ type: 'material' | 'assignment', id: number } | null>(null);
  const [lecturerClasses, setLecturerClasses] = useState<any[]>([]);
  const [targetClassId, setTargetClassId] = useState<number | null>(null);
  const [targetMeetings, setTargetMeetings] = useState<Meeting[]>([]);
  const [targetMeetingId, setTargetMeetingId] = useState<number | null>(null);

  // Forms
  const [newMeeting, setNewMeeting] = useState({ title: '', description: '', date: '' });
  const [newMaterial, setNewMaterial] = useState({ title: '', content: '', type: 'text' });
  const [newAssignment, setNewAssignment] = useState({ 
    title: '', description: '', due_date: '', type: 'latihan', work_type: 'individu' 
  });
  const [assignmentQuestions, setAssignmentQuestions] = useState<Question[]>([]);
  
  const [learningModel, setLearningModel] = useState('none');
  
  // New state structure for syntax configuration
  interface MaterialInput {
    title: string;
    content: string;
    type: string;
  }

  interface SyntaxStepConfig {
    id: string;
    title: string;
    selected: boolean;
    subSteps: string[];
    tools: string[];
    stepMaterials: MaterialInput[];
  }
  
  const [syntaxConfig, setSyntaxConfig] = useState<SyntaxStepConfig[]>([]);

  const pblSteps = [
    "Orientasi peserta didik pada masalah",
    "Mengorganisasikan peserta didik untuk belajar",
    "Membimbing penyelidikan individu maupun kelompok",
    "Mengembangkan dan menyajikan hasil karya",
    "Menganalisis dan mengevaluasi proses pemecahan masalah"
  ];

  const pjblSteps = [
    "Pertanyaan Mendasar",
    "Mendesain Perencanaan Produk",
    "Menyusun Jadwal Pembuatan",
    "Memonitor Keaktifan dan Perkembangan Proyek",
    "Menguji Hasil",
    "Evaluasi Pengalaman Belajar"
  ];

  const availableTools = [
    { id: 'material', label: 'Materi' },
    { id: 'assignment', label: 'Tugas' },
    { id: 'group_chat', label: 'Diskusi Kelompok' },
    { id: 'class_chat', label: 'Diskusi Kelas' }
  ];

  useEffect(() => {
    fetchClassDetails();
    fetchMeetings();
    fetchContent();
  }, [classId]);

  // Reset syntax config when model changes
  useEffect(() => {
    const steps = learningModel === 'pbl' ? pblSteps : learningModel === 'pjbl' ? pjblSteps : [];
    const initialConfig: SyntaxStepConfig[] = steps.map((step, idx) => ({
      id: `step-${idx}-${Date.now()}`,
      title: step,
      selected: true, // Default to selected for standard models
      subSteps: [],
      tools: [],
      stepMaterials: []
    }));
    setSyntaxConfig(initialConfig);
  }, [learningModel]);

  const fetchClassDetails = async () => {
    const res = await fetch(`/api/classes/${classId}`);
    if (res.ok) {
      const data = await res.json();
      setClassName(data.name);
    }
  };

  const fetchMeetings = async () => {
    const res = await fetch(`/api/classes/${classId}/meetings`);
    if (res.ok) {
      setMeetings(await res.json());
    }
  };

  const fetchContent = async () => {
    const res = await fetch(`/api/classes/${classId}/details`);
    if (res.ok) {
      const data = await res.json();
      setMaterials(data.materials);
      setAssignments(data.assignments);
    }
  };

  const handleCreateMeeting = async (e: React.FormEvent) => {
    e.preventDefault();
    
    // Filter only selected steps for storage and gather materials
    const finalSyntaxData: any[] = [];
    const materialsToCreate: any[] = [];

    syntaxConfig.forEach((config) => {
      if (config.selected) {
        finalSyntaxData.push({
          title: config.title,
          subSteps: config.subSteps,
          tools: config.tools
        });
        
        // Collect materials from this step
        if (config.stepMaterials && config.stepMaterials.length > 0) {
          config.stepMaterials.forEach(mat => {
            materialsToCreate.push({
              title: `${mat.title} (${config.title})`,
              content: mat.content,
              type: mat.type
            });
          });
        }
      }
    });

    const url = editingMeetingId ? `/api/meetings/${editingMeetingId}` : '/api/meetings';
    const method = editingMeetingId ? 'PUT' : 'POST';

    const res = await fetch(url, {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        ...newMeeting, 
        class_id: classId,
        learning_model: learningModel,
        learning_syntax: finalSyntaxData,
        materials: materialsToCreate
      }),
    });

    if (res.ok) {
      setShowMeetingForm(false);
      setNewMeeting({ title: '', description: '', date: '' });
      setLearningModel('none');
      setSyntaxConfig([]);
      setEditingMeetingId(null);
      fetchMeetings();
      if (!editingMeetingId) fetchContent(); 
    } else {
      alert('Gagal menyimpan pertemuan');
    }
  };

  const handleEditMeeting = (meeting: Meeting) => {
    setEditingMeetingId(meeting.id);
    setNewMeeting({
      title: meeting.title,
      description: meeting.description,
      date: meeting.date.split('T')[0]
    });
    setLearningModel(meeting.learning_model || 'none');
    
    // Parse syntax to populate config
    if (meeting.learning_model && meeting.learning_model !== 'none' && meeting.learning_syntax) {
      try {
        const parsedSyntax = JSON.parse(meeting.learning_syntax);
        let newConfig: SyntaxStepConfig[] = [];
        
        if (Array.isArray(parsedSyntax)) {
          newConfig = parsedSyntax.map((step: any, idx: number) => ({
            id: `step-${idx}-${Date.now()}`,
            title: step.title,
            selected: true,
            subSteps: step.subSteps || [],
            tools: step.tools || [],
            stepMaterials: []
          }));
        } else {
          // Handle old object format
          newConfig = Object.entries(parsedSyntax).map(([title, data]: [string, any], idx: number) => ({
            id: `step-${idx}-${Date.now()}`,
            title: title,
            selected: true,
            subSteps: data.subSteps || [],
            tools: data.tools || [],
            stepMaterials: []
          }));
        }
        setSyntaxConfig(newConfig);
      } catch (e) {
        console.error("Error parsing syntax for edit", e);
      }
    } else {
       // Reset if no model
       setSyntaxConfig([]);
    }

    setShowMeetingForm(true);
  };

  const handleDeleteMeeting = async (id: number) => {
    if (!confirm('Apakah Anda yakin ingin menghapus pertemuan ini? Semua materi dan tugas terkait akan ikut terhapus.')) return;

    try {
      const res = await fetch(`/api/meetings/${id}`, {
        method: 'DELETE'
      });

      if (res.ok) {
        fetchMeetings();
      } else {
        alert('Gagal menghapus pertemuan');
      }
    } catch (error) {
      console.error('Failed to delete meeting', error);
    }
  };

  const handleCreateMaterial = async (e: React.FormEvent) => {
    e.preventDefault();
    if (showMaterialForm === null && !editingMaterialId) return;

    const url = editingMaterialId ? `/api/materials/${editingMaterialId}` : '/api/materials';
    const method = editingMaterialId ? 'PUT' : 'POST';

    const res = await fetch(url, {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        ...newMaterial,
        class_id: classId,
        meeting_id: showMaterialForm
      })
    });

    if (res.ok) {
      setShowMaterialForm(null);
      setEditingMaterialId(null);
      setNewMaterial({ title: '', content: '', type: 'text' });
      fetchContent();
    } else {
      alert('Gagal menyimpan materi');
    }
  };

  const handleEditMaterial = (material: Material) => {
    setEditingMaterialId(material.id);
    setNewMaterial({
      title: material.title,
      content: material.content,
      type: material.type
    });
    setShowMaterialForm(material.meeting_id);
  };

  const handleDeleteMaterial = async (id: number) => {
    if (confirm('Hapus materi ini?')) {
      await fetch(`/api/materials/${id}`, { method: 'DELETE' });
      fetchContent();
    }
  };

  const handleDeleteAssignment = async (id: number) => {
    if (confirm('Hapus tugas ini?')) {
      await fetch(`/api/assignments/${id}`, { method: 'DELETE' });
      fetchContent();
    }
  };

  // Syntax Configuration Handlers
  const toggleStepSelection = (id: string) => {
    setSyntaxConfig(prev => prev.map(step => 
      step.id === id ? { ...step, selected: !step.selected } : step
    ));
  };

  const addCustomStep = () => {
    setSyntaxConfig(prev => [
      ...prev,
      {
        id: `step-custom-${Date.now()}`,
        title: 'Langkah Baru',
        selected: true,
        subSteps: [],
        tools: [],
        stepMaterials: []
      }
    ]);
  };

  const updateStepTitle = (id: string, title: string) => {
    setSyntaxConfig(prev => prev.map(step => 
      step.id === id ? { ...step, title } : step
    ));
  };

  const removeStep = (id: string) => {
    setSyntaxConfig(prev => prev.filter(step => step.id !== id));
  };

  const addSubStep = (id: string) => {
    setSyntaxConfig(prev => prev.map(step => 
      step.id === id ? { ...step, subSteps: [...step.subSteps, ''] } : step
    ));
  };

  const updateSubStep = (id: string, index: number, value: string) => {
    setSyntaxConfig(prev => prev.map(step => {
      if (step.id === id) {
        const newSubSteps = [...step.subSteps];
        newSubSteps[index] = value;
        return { ...step, subSteps: newSubSteps };
      }
      return step;
    }));
  };

  const removeSubStep = (id: string, index: number) => {
    setSyntaxConfig(prev => prev.map(step => {
      if (step.id === id) {
        const newSubSteps = step.subSteps.filter((_, i) => i !== index);
        return { ...step, subSteps: newSubSteps };
      }
      return step;
    }));
  };

  const toggleTool = (id: string, toolId: string) => {
    setSyntaxConfig(prev => prev.map(step => {
      if (step.id === id) {
        const currentTools = step.tools;
        const newTools = currentTools.includes(toolId)
          ? currentTools.filter(t => t !== toolId)
          : [...currentTools, toolId];
        
        let newMaterials = step.stepMaterials;
        if (toolId === 'material' && !currentTools.includes('material') && newMaterials.length === 0) {
          newMaterials = [{ title: '', content: '', type: 'text' }];
        }
        return { ...step, tools: newTools, stepMaterials: newMaterials };
      }
      return step;
    }));
  };

  const updateStepMaterial = (id: string, index: number, field: keyof MaterialInput, value: string) => {
    setSyntaxConfig(prev => prev.map(step => {
      if (step.id === id) {
        const newMaterials = [...step.stepMaterials];
        newMaterials[index] = { ...newMaterials[index], [field]: value };
        return { ...step, stepMaterials: newMaterials };
      }
      return step;
    }));
  };

  const addStepMaterial = (id: string) => {
    setSyntaxConfig(prev => prev.map(step => 
      step.id === id ? { ...step, stepMaterials: [...step.stepMaterials, { title: '', content: '', type: 'text' }] } : step
    ));
  };

  const removeStepMaterial = (id: string, index: number) => {
    setSyntaxConfig(prev => prev.map(step => {
      if (step.id === id) {
        const newMaterials = step.stepMaterials.filter((_, i) => i !== index);
        return { ...step, stepMaterials: newMaterials };
      }
      return step;
    }));
  };

  useEffect(() => {
    if (showShareModal && user) {
      fetchLecturerClasses();
    }
  }, [showShareModal, user]);

  useEffect(() => {
    if (targetClassId) {
      fetchTargetMeetings(targetClassId);
    }
  }, [targetClassId]);

  const fetchLecturerClasses = async () => {
    const res = await fetch(`/api/lecturer/${user?.id}/classes`);
    if (res.ok) setLecturerClasses(await res.json());
  };

  const fetchTargetMeetings = async (clsId: number) => {
    const res = await fetch(`/api/classes/${clsId}/meetings`);
    if (res.ok) setTargetMeetings(await res.json());
  };

  const handleShare = async () => {
    if (!shareItem || !targetClassId || !targetMeetingId) return;
    
    const endpoint = shareItem.type === 'material' ? '/api/materials/share' : '/api/assignments/share';
    const payload = {
      [`${shareItem.type}_id`]: shareItem.id,
      target_class_id: targetClassId,
      target_meeting_id: targetMeetingId
    };

    const res = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    if (res.ok) {
      alert('Berhasil dibagikan!');
      setShowShareModal(false);
      setShareItem(null);
      setTargetClassId(null);
      setTargetMeetingId(null);
    } else {
      alert('Gagal membagikan item.');
    }
  };

  const openShareModal = (type: 'material' | 'assignment', id: number) => {
    setShareItem({ type, id });
    setShowShareModal(true);
  };

  // Assignment Handlers
  const handleEditAssignment = async (assignment: Assignment) => {
    setEditingAssignmentId(assignment.id);
    setNewAssignment({
      title: assignment.title,
      description: assignment.description,
      due_date: assignment.due_date ? assignment.due_date.slice(0, 16) : '',
      type: assignment.type,
      work_type: assignment.work_type
    });
    
    // Fetch questions
    const res = await fetch(`/api/assignments/${assignment.id}`);
    if (res.ok) {
      const data = await res.json();
      if (data.questions) {
        setAssignmentQuestions(data.questions.map((q: any) => ({
          ...q,
          options: typeof q.options === 'string' ? JSON.parse(q.options) : q.options
        })));
      } else {
        setAssignmentQuestions([]);
      }
    }
    
    setShowAssignmentForm(assignment.meeting_id);
  };

  const handleCreateAssignment = async (e: React.FormEvent) => {
    e.preventDefault();
    if (showAssignmentForm === null && !editingAssignmentId) return;

    // If editing, use PUT. If creating (not implemented in this modal yet, but good to have), use POST.
    // Actually, creating new assignment is done via CreateAssignment page usually, but we can support it here too if needed.
    // For now, let's assume this modal is primarily for EDITING existing assignments or adding simple ones.
    // But wait, the user asked to "edit pada tugas /soal".
    
    // If editingAssignmentId is set, we are updating.
    if (editingAssignmentId) {
      const res = await fetch(`/api/assignments/${editingAssignmentId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          ...newAssignment,
          questions: assignmentQuestions
        })
      });

      if (res.ok) {
        setShowAssignmentForm(null);
        setEditingAssignmentId(null);
        setNewAssignment({ title: '', description: '', due_date: '', type: 'latihan', work_type: 'individu' });
        setAssignmentQuestions([]);
        fetchContent();
      } else {
        alert('Gagal menyimpan tugas');
      }
    }
  };

  // Question Helpers
  const addQuestion = () => {
    setAssignmentQuestions([...assignmentQuestions, { question_text: '', question_type: 'essay', options: [], correct_answer: '', points: 10 }]);
  };

  const updateQuestion = (index: number, field: keyof Question, value: any) => {
    const newQuestions = [...assignmentQuestions];
    newQuestions[index] = { ...newQuestions[index], [field]: value };
    setAssignmentQuestions(newQuestions);
  };

  const handleOptionChange = (qIndex: number, oIndex: number, value: string) => {
    const newQuestions = [...assignmentQuestions];
    const newOptions = [...newQuestions[qIndex].options];
    newOptions[oIndex] = value;
    newQuestions[qIndex].options = newOptions;
    setAssignmentQuestions(newQuestions);
  };

  const addOption = (qIndex: number) => {
    const newQuestions = [...assignmentQuestions];
    newQuestions[qIndex].options.push('');
    setAssignmentQuestions(newQuestions);
  };

  const removeQuestion = (index: number) => {
    const newQuestions = [...assignmentQuestions];
    newQuestions.splice(index, 1);
    setAssignmentQuestions(newQuestions);
  };

  return (
    <div className="pb-20">
      <div className="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <Link to="/lecturer/courses" className="flex items-center text-gray-600 hover:text-indigo-600 transition-colors">
          <ArrowLeft size={20} className="mr-2" /> Kembali ke Daftar Kelas
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
            onClick={openRAGDocumentModal}
            className="flex items-center gap-2 px-4 py-2 bg-white border border-indigo-100 rounded-xl text-indigo-600 font-bold text-sm hover:bg-indigo-50 transition-all shadow-sm active:scale-95"
          >
            <Database size={16} />
            Input Dokumen RAG
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

      <div className="flex justify-between items-center mb-8">
        <div>
          <h1 className="text-3xl font-bold text-gray-800">{className}</h1>
          <p className="text-gray-500">Manajemen Pertemuan & Materi</p>
        </div>
        <button 
          onClick={() => {
            setEditingMeetingId(null);
            setNewMeeting({ title: '', description: '', date: '' });
            setLearningModel('none');
            setSyntaxConfig([]);
            setShowMeetingForm(true);
          }}
          className="bg-indigo-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-indigo-700 shadow-sm"
        >
          <Plus size={20} /> Tambah Pertemuan
        </button>
      </div>

      {/* Meeting Form Modal */}
      {showMeetingForm && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-hidden">
          <div className="bg-white p-6 rounded-xl shadow-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center mb-4">
              <h3 className="text-lg font-semibold">{editingMeetingId ? 'Edit Pertemuan' : 'Buat Pertemuan Baru'}</h3>
              <button onClick={() => setShowMeetingForm(false)}><X size={20} /></button>
            </div>
            <form onSubmit={handleCreateMeeting} className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input
                  placeholder="Judul Pertemuan"
                  value={newMeeting.title}
                  onChange={e => setNewMeeting({...newMeeting, title: e.target.value})}
                  className="border p-2 rounded w-full"
                  required
                />
                <input
                  type="date"
                  value={newMeeting.date}
                  onChange={e => setNewMeeting({...newMeeting, date: e.target.value})}
                  className="border p-2 rounded w-full"
                  required
                />
              </div>
              <textarea
                placeholder="Deskripsi / Agenda"
                value={newMeeting.description}
                onChange={e => setNewMeeting({...newMeeting, description: e.target.value})}
                className="border p-2 rounded w-full h-24"
              />

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Model Pembelajaran</label>
                <select 
                  value={learningModel}
                  onChange={(e) => setLearningModel(e.target.value)}
                  className="border p-2 rounded w-full"
                >
                  <option value="none">Tidak Ada / Ceramah</option>
                  <option value="custom">Custom (Sintaks Mandiri)</option>
                  <option value="pbl">Problem Based Learning (PBL)</option>
                  <option value="pjbl">Project Based Learning (PjBL)</option>
                </select>
              </div>

              {learningModel !== 'none' && (
                <div className="space-y-4 mt-4">
                  <div className="flex justify-between items-center border-b pb-2">
                    <h4 className="font-semibold text-gray-800">Konfigurasi Sintaks {learningModel.toUpperCase()}</h4>
                    <button 
                      type="button"
                      onClick={addCustomStep}
                      className="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded hover:bg-indigo-200 flex items-center gap-1"
                    >
                      <Plus size={14} /> Tambah Tahapan
                    </button>
                  </div>
                  <p className="text-xs text-gray-500">Sesuaikan tahapan sintaks, sub-aktivitas, dan fitur pendukung untuk pertemuan ini.</p>
                  
                  <div className="space-y-4 max-h-80 overflow-y-auto pr-2">
                    {syntaxConfig.map((config) => (
                      <div key={config.id} className={`border rounded-lg p-3 transition-all ${config.selected ? 'bg-indigo-50 border-indigo-200' : 'bg-gray-50 border-gray-200'}`}>
                        <div className="flex items-center gap-3">
                          <input 
                            type="checkbox" 
                            checked={config.selected} 
                            onChange={() => toggleStepSelection(config.id)}
                            className="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500"
                          />
                          <input 
                            value={config.title}
                            onChange={(e) => updateStepTitle(config.id, e.target.value)}
                            className={`flex-1 font-medium text-sm bg-transparent border-b border-transparent focus:border-indigo-300 focus:outline-none ${config.selected ? 'text-indigo-900 font-bold' : 'text-gray-500'}`}
                            placeholder="Nama Tahapan..."
                          />
                          <button type="button" onClick={() => removeStep(config.id)} className="text-gray-400 hover:text-red-500 p-1">
                            <Trash2 size={16} />
                          </button>
                        </div>

                        {config.selected && (
                          <div className="mt-3 pl-7 space-y-3 animate-in slide-in-from-top-2 duration-200">
                            {/* Sub-steps */}
                            <div>
                              <label className="text-xs font-bold text-gray-600 block mb-1">Sub-Aktivitas / Kegiatan:</label>
                              <div className="space-y-2">
                                {config.subSteps.map((subStep, idx) => (
                                  <div key={idx} className="flex gap-2">
                                    <input 
                                      value={subStep}
                                      onChange={(e) => updateSubStep(config.id, idx, e.target.value)}
                                      placeholder="Deskripsi kegiatan..."
                                      className="flex-1 text-sm border border-gray-300 rounded px-2 py-1 focus:border-indigo-500 focus:outline-none"
                                    />
                                    <button type="button" onClick={() => removeSubStep(config.id, idx)} className="text-red-500 hover:bg-red-50 p-1 rounded">
                                      <X size={14} />
                                    </button>
                                  </div>
                                ))}
                                <button 
                                  type="button" 
                                  onClick={() => addSubStep(config.id)}
                                  className="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1 mt-1"
                                >
                                  <Plus size={12} /> Tambah Kegiatan
                                </button>
                              </div>
                            </div>

                            {/* Tools */}
                            <div>
                              <label className="text-xs font-bold text-gray-600 block mb-1">Fitur Pendukung:</label>
                              <div className="flex flex-wrap gap-2">
                                {availableTools.map(tool => (
                                  <label key={tool.id} className="inline-flex items-center gap-1.5 bg-white border border-gray-200 px-2 py-1 rounded text-xs cursor-pointer hover:border-indigo-300">
                                    <input 
                                      type="checkbox"
                                      checked={config.tools.includes(tool.id)}
                                      onChange={() => toggleTool(config.id, tool.id)}
                                      className="rounded text-indigo-600 focus:ring-0 w-3 h-3"
                                    />
                                    {tool.label}
                                  </label>
                                ))}
                              </div>
                            </div>

                            {/* Material Inputs */}
                            {config.tools.includes('material') && (
                              <div className="bg-indigo-50/50 p-3 rounded border border-indigo-100 space-y-3">
                                <label className="text-xs font-bold text-indigo-700 block">Materi Pendukung Tahap Ini:</label>
                                {config.stepMaterials.map((mat, idx) => (
                                  <div key={idx} className="space-y-2 border-b border-indigo-100 pb-2 last:border-0 last:pb-0">
                                    <div className="flex gap-2">
                                      <input 
                                        value={mat.title}
                                        onChange={(e) => updateStepMaterial(config.id, idx, 'title', e.target.value)}
                                        placeholder="Judul Materi"
                                        className="flex-1 text-sm border border-gray-300 rounded px-2 py-1"
                                      />
                                      <button type="button" onClick={() => removeStepMaterial(config.id, idx)} className="text-red-500 hover:bg-red-50 p-1 rounded">
                                        <X size={14} />
                                      </button>
                                    </div>
                                    <textarea 
                                      value={mat.content}
                                      onChange={(e) => updateStepMaterial(config.id, idx, 'content', e.target.value)}
                                      placeholder="Isi materi atau link..."
                                      className="w-full text-sm border border-gray-300 rounded px-2 py-1 h-16"
                                    />
                                  </div>
                                ))}
                                <button 
                                  type="button" 
                                  onClick={() => addStepMaterial(config.id)}
                                  className="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1"
                                >
                                  <Plus size={12} /> Tambah Materi Lain
                                </button>
                              </div>
                            )}
                          </div>
                        )}
                      </div>
                    ))}
                  </div>
                </div>
              )}

              <div className="flex justify-end gap-2 pt-4">
                <button type="button" onClick={() => setShowMeetingForm(false)} className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Batal</button>
                <button type="submit" className="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Simpan</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Material Form Modal */}
      {showMaterialForm !== null && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white p-6 rounded-xl shadow-lg w-full max-w-md">
            <h3 className="text-lg font-semibold mb-4">{editingMaterialId ? 'Edit Materi' : 'Tambah Materi'}</h3>
            <form onSubmit={handleCreateMaterial} className="space-y-4">
              <input
                placeholder="Judul Materi"
                value={newMaterial.title}
                onChange={e => setNewMaterial({...newMaterial, title: e.target.value})}
                className="border p-2 rounded w-full"
                required
              />
              <textarea
                placeholder="Isi Materi / Link"
                value={newMaterial.content}
                onChange={e => setNewMaterial({...newMaterial, content: e.target.value})}
                className="border p-2 rounded w-full h-32"
              />
              <div className="flex justify-end gap-2">
                <button type="button" onClick={() => setShowMaterialForm(null)} className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Batal</button>
                <button type="submit" className="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Simpan</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Share Modal */}
      {showShareModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white p-6 rounded-xl shadow-lg w-full max-w-md">
            <h3 className="text-lg font-semibold mb-4">Bagikan ke Kelas Lain</h3>
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Pilih Kelas Tujuan</label>
                <select 
                  className="border p-2 rounded w-full"
                  onChange={(e) => setTargetClassId(Number(e.target.value))}
                  value={targetClassId || ''}
                >
                  <option value="">-- Pilih Kelas --</option>
                  {lecturerClasses.map(c => (
                    <option key={c.id} value={c.id}>{c.name} ({c.code})</option>
                  ))}
                </select>
              </div>
              
              {targetClassId && (
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Pilih Pertemuan</label>
                  <select 
                    className="border p-2 rounded w-full"
                    onChange={(e) => setTargetMeetingId(Number(e.target.value))}
                    value={targetMeetingId || ''}
                  >
                    <option value="">-- Pilih Pertemuan --</option>
                    {targetMeetings.map(m => (
                      <option key={m.id} value={m.id}>{m.title}</option>
                    ))}
                  </select>
                </div>
              )}

              <div className="flex justify-end gap-2 pt-2">
                <button onClick={() => setShowShareModal(false)} className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Batal</button>
                <button 
                  onClick={handleShare} 
                  disabled={!targetClassId || !targetMeetingId}
                  className="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 disabled:opacity-50"
                >
                  Bagikan
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Assignment Edit Modal */}
      {showAssignmentForm !== null && editingAssignmentId && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-hidden">
          <div className="bg-white p-6 rounded-xl shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center mb-4">
              <h3 className="text-lg font-semibold">Edit Tugas / Soal</h3>
              <button onClick={() => { setShowAssignmentForm(null); setEditingAssignmentId(null); }}><X size={20} /></button>
            </div>
            <form onSubmit={handleCreateAssignment} className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input
                  placeholder="Judul Tugas"
                  value={newAssignment.title}
                  onChange={e => setNewAssignment({...newAssignment, title: e.target.value})}
                  className="border p-2 rounded w-full"
                  required
                />
                <input
                  type="datetime-local"
                  value={newAssignment.due_date}
                  onChange={e => setNewAssignment({...newAssignment, due_date: e.target.value})}
                  className="border p-2 rounded w-full"
                />
                <select 
                  value={newAssignment.type}
                  onChange={e => setNewAssignment({...newAssignment, type: e.target.value})}
                  className="border p-2 rounded w-full"
                >
                  <option value="latihan">Latihan</option>
                  <option value="tugas">Tugas</option>
                  <option value="ujian">Ujian</option>
                </select>
                <select 
                  value={newAssignment.work_type}
                  onChange={e => setNewAssignment({...newAssignment, work_type: e.target.value})}
                  className="border p-2 rounded w-full"
                >
                  <option value="individu">Individu</option>
                  <option value="kelompok">Kelompok</option>
                </select>
                <div className="md:col-span-2">
                  <textarea
                    placeholder="Deskripsi"
                    value={newAssignment.description}
                    onChange={e => setNewAssignment({...newAssignment, description: e.target.value})}
                    className="border p-2 rounded w-full h-20"
                  />
                </div>
              </div>

              {/* Questions Editor */}
              <div className="space-y-4 border-t pt-4">
                <div className="flex justify-between items-center">
                  <h4 className="font-bold text-gray-700">Daftar Soal</h4>
                  <button type="button" onClick={addQuestion} className="text-sm bg-indigo-50 text-indigo-600 px-3 py-1 rounded hover:bg-indigo-100 font-medium flex items-center gap-1">
                    <Plus size={16} /> Tambah Soal
                  </button>
                </div>
                
                {assignmentQuestions.map((q, index) => (
                  <div key={index} className="bg-gray-50 p-4 rounded-lg border border-gray-200 relative">
                    <button type="button" onClick={() => removeQuestion(index)} className="absolute top-2 right-2 text-gray-400 hover:text-red-500">
                      <Trash2 size={16} />
                    </button>
                    <div className="grid grid-cols-1 md:grid-cols-12 gap-3">
                      <div className="md:col-span-8">
                        <input 
                          value={q.question_text}
                          onChange={e => updateQuestion(index, 'question_text', e.target.value)}
                          className="border p-2 rounded w-full text-sm"
                          placeholder="Pertanyaan..."
                        />
                      </div>
                      <div className="md:col-span-2">
                        <select 
                          value={q.question_type}
                          onChange={e => updateQuestion(index, 'question_type', e.target.value)}
                          className="border p-2 rounded w-full text-sm"
                        >
                          <option value="essay">Essay</option>
                          <option value="multiple_choice">Pilgan</option>
                        </select>
                      </div>
                      <div className="md:col-span-2">
                        <input 
                          type="number"
                          value={q.points}
                          onChange={e => updateQuestion(index, 'points', Number(e.target.value))}
                          className="border p-2 rounded w-full text-sm"
                          placeholder="Poin"
                        />
                      </div>
                    </div>

                    {q.question_type === 'multiple_choice' && (
                      <div className="mt-2 pl-4 border-l-2 border-indigo-200 space-y-2">
                        {q.options.map((opt, optIndex) => (
                          <div key={optIndex} className="flex items-center gap-2">
                            <input 
                              type="radio" 
                              checked={q.correct_answer === opt}
                              onChange={() => updateQuestion(index, 'correct_answer', opt)}
                            />
                            <input 
                              value={opt}
                              onChange={e => handleOptionChange(index, optIndex, e.target.value)}
                              className="border p-1 rounded flex-1 text-xs"
                              placeholder={`Opsi ${optIndex + 1}`}
                            />
                          </div>
                        ))}
                        <button type="button" onClick={() => addOption(index)} className="text-xs text-indigo-600 hover:underline">+ Opsi</button>
                      </div>
                    )}
                  </div>
                ))}
              </div>

              <div className="flex justify-end gap-2 pt-4 border-t">
                <button type="button" onClick={() => { setShowAssignmentForm(null); setEditingAssignmentId(null); }} className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Batal</button>
                <button type="submit" className="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Simpan Perubahan</button>
              </div>
            </form>
          </div>
        </div>
      )}

      <div className="space-y-6">
        {meetings.length === 0 ? (
          <div className="text-center py-12 bg-white rounded-xl border border-dashed border-gray-300">
            <Calendar className="mx-auto text-gray-400 mb-4" size={48} />
            <h3 className="text-lg font-medium text-gray-900">Belum ada pertemuan</h3>
            <p className="text-gray-500">Tambahkan pertemuan untuk memulai pembelajaran.</p>
          </div>
        ) : (
          meetings.map((meeting, index) => {
            const meetingMaterials = materials.filter(m => m.meeting_id === meeting.id);
            const meetingAssignments = assignments.filter(a => a.meeting_id === meeting.id);
            
            // Helper to parse syntax safely
            let syntax: any[] = [];
            try {
              const parsed = JSON.parse(meeting.learning_syntax || '[]');
              if (Array.isArray(parsed)) {
                syntax = parsed;
              } else {
                // Handle old object format
                syntax = Object.entries(parsed).map(([title, data]: [string, any]) => ({
                  title,
                  ...(data as any)
                }));
              }
            } catch (e) {}

            return (
              <div key={meeting.id} className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div className="p-6 border-b border-gray-100 flex justify-between items-start bg-gray-50">
                  <div className="flex gap-4">
                    <div className="bg-white p-3 rounded-lg text-center min-w-[60px] shadow-sm border border-gray-100">
                      <span className="block text-indigo-600 font-bold text-xl">
                        {index + 1}
                      </span>
                      <span className="block text-gray-400 text-[10px] uppercase font-bold">
                        Pertemuan
                      </span>
                    </div>
                    <div className="flex-1">
                      <h3 className="text-xl font-bold text-gray-800">{meeting.title}</h3>
                      <p className="text-sm text-gray-500 mb-2">{new Date(meeting.date).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
                      
                      {meeting.learning_model !== 'none' && syntax.length > 0 ? (
                        <div className="mt-4 space-y-4">
                          <div className="inline-block bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded font-bold uppercase mb-2">
                            Model: {meeting.learning_model}
                          </div>
                          <div className="space-y-3">
                            {syntax.map((stepData, idx) => (
                              <div key={idx} className="border-l-2 border-indigo-300 pl-3 ml-1">
                                <h5 className="font-semibold text-gray-800 text-sm">{stepData.title}</h5>
                                
                                {/* Sub-steps */}
                                {stepData.subSteps && stepData.subSteps.length > 0 && (
                                  <ul className="mt-1 space-y-1">
                                    {stepData.subSteps.map((sub: string, i: number) => (
                                      <li key={i} className="text-sm text-gray-600 flex items-start gap-2">
                                        <span className="mt-1.5 w-1 h-1 bg-gray-400 rounded-full flex-shrink-0"></span>
                                        {sub}
                                      </li>
                                    ))}
                                  </ul>
                                )}

                                {/* Tools Menu */}
                                {stepData.tools && stepData.tools.length > 0 && (
                                  <div className="flex flex-wrap gap-2 mt-2">
                                    {stepData.tools.map((tool: string) => {
                                      const toolLabel = availableTools.find(t => t.id === tool)?.label || tool;
                                      return (
                                        <span key={tool} className="text-[10px] bg-white border border-gray-200 px-2 py-1 rounded-full text-gray-500 flex items-center gap-1">
                                          {tool === 'material' && <FileText size={10} />}
                                          {tool === 'assignment' && <CheckSquare size={10} />}
                                          {(tool === 'group_chat' || tool === 'class_chat') && <FileText size={10} />} 
                                          {toolLabel}
                                        </span>
                                      );
                                    })}
                                  </div>
                                )}
                              </div>
                            ))}
                          </div>
                        </div>
                      ) : (
                        <p className="text-gray-600 mt-2 text-sm">{meeting.description}</p>
                      )}
                    </div>
                  </div>
                  <div className="flex gap-2">
                    <button 
                      onClick={() => handleEditMeeting(meeting)}
                      className="text-gray-400 hover:text-indigo-600 p-2"
                    >
                      <Edit2 size={18} />
                    </button>
                    <button 
                      onClick={() => handleDeleteMeeting(meeting.id)}
                      className="text-gray-400 hover:text-red-600 p-2"
                    >
                      <Trash2 size={18} />
                    </button>
                  </div>
                </div>

                <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                  {/* Materials Column */}
                  <div>
                    <div className="flex justify-between items-center mb-3">
                      <h4 className="font-semibold text-gray-700 flex items-center gap-2">
                        <FileText size={16} /> Materi
                      </h4>
                      <button 
                        onClick={() => {
                          setEditingMaterialId(null);
                          setNewMaterial({ title: '', content: '', type: 'text' });
                          setShowMaterialForm(meeting.id);
                        }}
                        className="text-xs bg-indigo-50 text-indigo-600 px-2 py-1 rounded hover:bg-indigo-100 font-medium"
                      >
                        + Tambah
                      </button>
                    </div>
                    {meetingMaterials.length === 0 ? (
                      <p className="text-sm text-gray-400 italic">Belum ada materi.</p>
                    ) : (
                      <ul className="space-y-2">
                        {meetingMaterials.map(m => (
                          <li key={m.id} className="text-sm bg-gray-50 p-2 rounded border border-gray-100 text-gray-700 flex justify-between items-center group">
                            <span>{m.title}</span>
                            <div className="flex gap-1 opacity-0 group-hover:opacity-100 transition">
                              <button onClick={() => openShareModal('material', m.id)} className="text-gray-400 hover:text-blue-600 p-1" title="Bagikan">
                                <Share2 size={14} />
                              </button>
                              <button onClick={() => handleEditMaterial(m)} className="text-gray-400 hover:text-indigo-600 p-1" title="Edit">
                                <Edit2 size={14} />
                              </button>
                              <button onClick={() => handleDeleteMaterial(m.id)} className="text-gray-400 hover:text-red-600 p-1" title="Hapus">
                                <Trash2 size={14} />
                              </button>
                            </div>
                          </li>
                        ))}
                      </ul>
                    )}
                  </div>

                  {/* Assignments Column */}
                  <div>
                    <div className="flex justify-between items-center mb-3">
                      <h4 className="font-semibold text-gray-700 flex items-center gap-2">
                        <CheckSquare size={16} /> Tugas / Soal
                      </h4>
                      <Link 
                        to="/lecturer/create-assignment"
                        className="text-xs bg-indigo-50 text-indigo-600 px-2 py-1 rounded hover:bg-indigo-100 font-medium"
                      >
                        + Input Soal
                      </Link>
                    </div>
                    {meetingAssignments.length === 0 ? (
                      <p className="text-sm text-gray-400 italic">Belum ada tugas.</p>
                    ) : (
                      <ul className="space-y-2">
                        {meetingAssignments.map(a => (
                          <li key={a.id} className="text-sm bg-gray-50 p-2 rounded border border-gray-100 text-gray-700 flex justify-between items-center group">
                            <div className="flex flex-col">
                              <span>{a.title}</span>
                              <div className="flex gap-1 mt-1">
                                <span className="text-[10px] bg-white border px-1 rounded text-gray-500">{a.type}</span>
                                <span className="text-[10px] bg-white border px-1 rounded text-blue-500">{a.work_type || 'Individu'}</span>
                              </div>
                            </div>
                            <div className="flex items-center gap-2">
                              <Link 
                                to={`/lecturer/assignments/${a.id}/submissions`} 
                                className="flex items-center gap-1 text-xs bg-white text-green-600 px-2 py-1 rounded border border-green-200 hover:bg-green-50 transition shadow-sm font-medium"
                              >
                                <CheckCircle size={12} /> Periksa
                              </Link>
                              <div className="flex gap-1 opacity-0 group-hover:opacity-100 transition">
                                <button onClick={() => openShareModal('assignment', a.id)} className="text-gray-400 hover:text-blue-600 p-1" title="Bagikan">
                                  <Share2 size={14} />
                                </button>
                                <button onClick={() => handleEditAssignment(a)} className="text-gray-400 hover:text-indigo-600 p-1" title="Edit">
                                  <Edit2 size={14} />
                                </button>
                                <button onClick={() => handleDeleteAssignment(a.id)} className="text-gray-400 hover:text-red-600 p-1" title="Hapus">
                                  <Trash2 size={14} />
                                </button>
                              </div>
                            </div>
                          </li>
                        ))}
                      </ul>
                    )}
                  </div>
                </div>
              </div>
            );
          })
        )}
      </div>
    </div>
  );
}
