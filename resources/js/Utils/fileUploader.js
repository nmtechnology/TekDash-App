import axios from 'axios';

/**
 * Upload files to a specific work order using a POST request
 * 
 * @param {number} workOrderId - The ID of the work order
 * @param {FileList|File[]} files - The files to upload
 * @returns {Promise} - Promise containing the server response
 */
export const uploadFilesToWorkOrder = async (workOrderId, files) => {
    // Create a FormData object
    const formData = new FormData();
    
    // Append each file to the FormData object with the correct name (files[])
    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }

    // Make sure CSRF token is included
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Send a POST request with the FormData
    try {
        const response = await axios.post(`/work-orders/${workOrderId}/update-images`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'X-CSRF-TOKEN': token,
            }
        });
        
        return response.data;
    } catch (error) {
        console.error('Error uploading files:', error);
        throw error;
    }
};
