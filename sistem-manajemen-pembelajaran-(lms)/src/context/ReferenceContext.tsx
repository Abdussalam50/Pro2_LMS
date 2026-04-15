import React, { createContext, useContext, useState } from 'react';
import ReferenceModal from '../components/ReferenceModal';
import RAGChatbotModal from '../components/RAGChatbotModal';
import RAGDocumentModal from '../components/RAGDocumentModal';

interface ReferenceContextType {
  openReferenceModal: () => void;
  openRAGChatbot: () => void;
  openRAGDocumentModal: () => void;
}

const ReferenceContext = createContext<ReferenceContextType | undefined>(undefined);

export function ReferenceProvider({ children }: { children: React.ReactNode }) {
  const [isRefOpen, setIsRefOpen] = useState(false);
  const [isChatOpen, setIsChatOpen] = useState(false);
  const [isRAGDocOpen, setIsRAGDocOpen] = useState(false);

  const openReferenceModal = () => setIsRefOpen(true);
  const closeReferenceModal = () => setIsRefOpen(false);

  const openRAGChatbot = () => setIsChatOpen(true);
  const closeRAGChatbot = () => setIsChatOpen(false);

  const openRAGDocumentModal = () => setIsRAGDocOpen(true);
  const closeRAGDocumentModal = () => setIsRAGDocOpen(false);

  return (
    <ReferenceContext.Provider value={{ openReferenceModal, openRAGChatbot, openRAGDocumentModal }}>
      {children}
      <ReferenceModal isOpen={isRefOpen} onClose={closeReferenceModal} />
      <RAGChatbotModal isOpen={isChatOpen} onClose={closeRAGChatbot} />
      <RAGDocumentModal isOpen={isRAGDocOpen} onClose={closeRAGDocumentModal} />
    </ReferenceContext.Provider>
  );
}

export function useReference() {
  const context = useContext(ReferenceContext);
  if (context === undefined) {
    throw new Error('useReference must be used within a ReferenceProvider');
  }
  return context;
}
