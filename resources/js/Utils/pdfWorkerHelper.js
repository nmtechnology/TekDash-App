import * as pdfjsLib from 'pdfjs-dist';

export const initPdfWorker = () => {
  pdfjsLib.GlobalWorkerOptions.workerSrc = '/js/pdf.worker.min.js';
};
