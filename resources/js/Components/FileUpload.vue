<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="w-96 rounded-lg shadow-lg bg-secondary p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">
                    Upload Files
                </h2>
                <button
                    @click="onClose"
                    class="p-1 rounded-lg hover:bg-secondary text-accent"
                >
                    <X size="20" class="text-gray-900"/>
                </button>
            </div>

            <div class="border-2 border-dashed rounded-lg p-8 text-center border-secondary">
                <Upload
                    :class="`mx-auto mb-4 text-accent`"
                    size="32"
                />
                <p class="text-accent">
                    Drag and drop files here, or click to select
                </p>
                <input type="file" ref="fileInput" class="hidden" @change="handleFileUpload" multiple />
                <button @click="triggerFileInput" class="mt-4 px-4 py-2 bg-accent text-white rounded-lg hover:bg-blue-600">
                    Select Files
                </button>
            </div>

            <div v-if="uploadedFiles.length > 0" class="mt-4">
                <h3 class="text-lg font-semibold text-gray-900">Uploaded Files</h3>
                <ul>
                    <li v-for="file in uploadedFiles" :key="file.id" class="text-accent ml-4 space-y-2">
                        <a :href="file.url" target="_blank">{{ file.name }}</a>
                    </li>
                </ul>
                <button @click="generateContext" class="mt-4 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                    Generate Context
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { X, Upload } from "lucide-vue-next";
import axios from "axios";
import { ref } from "vue";

export default {
    name: "FileUpload",
    props: {
        onClose: {
            type: Function,
            required: true,
        },
        currentChatId: {
            type: [String, Number],
            default: null
        },
    },
    components: {
        X,
        Upload,
    },
    setup(props) {
        const fileInput = ref(null);
        const uploadedFiles = ref([]);

        const triggerFileInput = () => {
            fileInput.value.click();
        };

        const handleFileUpload = async (event) => {
            const files = event.target.files;
            if (files.length > 0) {
                const formData = new FormData();
                for (let i = 0; i < files.length; i++) {
                    formData.append('file', files[i]);
                }

                try {
                    const response = await axios.post('/api/upload', formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data',
                        },
                    });
                    const fileId = response.data.file_id;
                    const filePreviewResponse = await axios.get(`/files/${fileId}/preview`, {
                        responseType: 'blob',
                    });
                    const fileURL = URL.createObjectURL(filePreviewResponse.data);

                    // Store the response and file URL in uploadedFiles
                    uploadedFiles.value.push({
                        id: fileId,
                        url: fileURL,
                        name: response.data.original_name,
                    });
                } catch (error) {
                    console.error('Error uploading file:', error);
                }
            }
        };

        const generateContext = async () => {
            const fileIds = uploadedFiles.value.map(file => file.id);
            try {
                const response = await axios.post('/api/generate-context', {
                    file_ids: fileIds,
                    chat_id: props.currentChatId
                });
                console.log('Context generated successfully:', response.data);
                props.onClose();
            } catch (error) {
                console.error('Error generating context:', error);
            }
        };

        const onClose = () => {
            props.onClose();
        };

        return {
            fileInput,
            uploadedFiles,
            triggerFileInput,
            handleFileUpload,
            generateContext,
            onClose,
        };
    },
};
</script>
