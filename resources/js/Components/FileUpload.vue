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
<!--                        <a :href="file.url" target="_blank">{{ file.name }}</a>-->
                        <div class="flex justify-between group">
                            <a @click="previewFile(file)" class="group-hover:text-red-500">{{ file.name }}</a>
                            <button @click="deleteFile(file)" class="group-hover:text-red-500">
                                <X size="20" class="text-gray-900"/>
                            </button>
                        </div>
                    </li>
                </ul>
                <button @click="createCollection" class="mt-4 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                    Create collection
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { X, Upload } from "lucide-vue-next";
import {onMounted, ref} from "vue";

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
        onCollectionCreated: {
            type: Function,
            default: () => {}
        }
    },
    components: {
        X,
        Upload,
    },
    setup(props) {
        const fileInput = ref(null);
        const uploadedFiles = ref([]); 
        const collectionInfo = ref(null);
        const COLLECTIONS_STORAGE_KEY = 'chat_collections';
        
        const triggerFileInput = async () => {
            fileInput.value.click();
        };
        
        onMounted(async () => {
            await fetchFiles();
            
            loadCollectionInfo();
        });
        
        const loadCollectionInfo = () => {
        try {
            const storedCollections = localStorage.getItem(COLLECTIONS_STORAGE_KEY);
            if (storedCollections) {
                const collections = JSON.parse(storedCollections);
                const chatCollection = collections[props.currentChatId];
                if (chatCollection) {
                    collectionInfo.value = chatCollection;

                    if (props.onCollectionCreated && typeof props.onCollectionCreated === 'function') {
                        props.onCollectionCreated(chatCollection);
                    }
                }
            }
                } catch (error) {
                    console.error('Error loading collection info from localStorage:', error);
            }
        };
        
        const saveCollectionInfo = (collectionData) => {
            try {
                const storedCollections = localStorage.getItem(COLLECTIONS_STORAGE_KEY);
                const collections = storedCollections ? JSON.parse(storedCollections) : {};
                
                collections[props.currentChatId] = collectionData;
                
                localStorage.setItem(COLLECTIONS_STORAGE_KEY, JSON.stringify(collections));
                
                collectionInfo.value = collectionData;
            } catch (error) {
                console.error('Error saving collection info to localStorage:', error);
            }
        };

        const fetchFiles = async () => {
            try {
                // Получение массива файлов.
                let response = await axios.get(`/api/files/${props.currentChatId}`);
                if (response.data && response.data.documents) {
                    // Преобразуем в массив объектов
                    uploadedFiles.value = response.data.documents.map(doc => ({
                        id: doc.id,
                        name: doc.original_name,
                        url: doc.path
                    }));
                } else {
                    console.error('Неожиданный формат ответа:', response.data);
                    uploadedFiles.value = []
                    this.error = 'Ошибка получения файлов: неверный формат данных';
                }

            } catch (e) {
                console.log(e.message)
            }
        }

        const handleFileUpload = async (event) => {
            const files = event.target.files;
            if (files.length > 0) {
                const formData = new FormData();
                // file:, file:
                formData.append('chat_id', props.currentChatId);
                for (let i = 0; i < files.length; i++) {
                    formData.append('files[]', files[i]);
                }

                try {
                    const response = await axios.post(`/api/files/${props.currentChatId}`, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data',
                        },
                    });
                    await fetchFiles();

                } catch (error) {
                    console.error('Error uploading file:', error);
                }
            }
        };

        const deleteFile = async (file) => {
            try {
                let response = await axios.delete(`/api/files/${props.currentChatId}/${file.id}`)
                let collection_response = await axios.delete(`/api/collection/${props.currentChatId}`)
                
                if (collection_response.status === 200 || collection_response.status === 204) {
                    removeCollectionInfo();
                }
                
                await fetchFiles();
            } catch (e) {
                console.log(e.message)
            }
        }
        
        const removeCollectionInfo = () => {
            try {
                const storedCollections = localStorage.getItem(COLLECTIONS_STORAGE_KEY);
                if (storedCollections) {
                    const collections = JSON.parse(storedCollections);
                    if (collections[props.currentChatId]) {
                        delete collections[props.currentChatId];
                        localStorage.setItem(COLLECTIONS_STORAGE_KEY, JSON.stringify(collections));
                        collectionInfo.value = null;
                    }
                }
            } catch (error) {
                console.error('Error removing collection info from localStorage:', error);
            }
        };

        const createCollection = async () => {
            try {
                // Create the collection
                const response = await axios.post('/api/collection/create', {
                    chat_id: parseInt(props.currentChatId)
                });
                
                console.log('Collection created:', response.data.collection);
                
                const collectionName = `collection-${props.currentChatId}`;
                const collectionParams = {
                    use_local_collection: true,
                    local_collection: collectionName,
                    collection_id: response.data.collection.id,
                    created_at: new Date().toISOString()
                };
                
                saveCollectionInfo(collectionParams);
                
                props.onCollectionCreated(collectionParams);
                
                props.onClose();
            } catch (error) {
                console.error('Error creating collection:', error);
            }
        };

        const onClose = () => {
            props.onClose();
        };

        const previewFile = async (file) => {
            try {
                let response = await axios.get(`/api/files/${props.currentChatId}/${file.id}`, {
                    responseType: 'blob', // Указываем, что ожидаем бинарные данные
                });

                // Проверяем, что ответ содержит данные
                if (response.data) {
                    // Получаем тип файла из заголовка или имени файла
                    const contentType = response.headers['content-type'];
                    const fileExtension = file.name.split('.').pop().toLowerCase();

                    // Проверяем, является ли файл PDF
                    if (contentType === 'application/pdf' || fileExtension === 'pdf') {
                        // Создаем URL для BLOB
                        const url = window.URL.createObjectURL(response.data);

                        // Открываем PDF в новом окне или вкладке
                        const newWindow = window.open(url);
                        if (!newWindow) {
                            alert('Пожалуйста, разрешите всплывающие окна для этого сайта.');
                        }
                    } else {
                        // Для других типов файлов инициируем скачивание
                        const url = window.URL.createObjectURL(response.data);
                        const link = document.createElement('a');
                        link.href = url;
                        link.setAttribute('download', file.name || 'download'); // Указываем имя файла для скачивания

                        // Добавляем элемент на страницу
                        document.body.appendChild(link);
                        link.click(); // Инициируем клик для скачивания

                        // Удаляем элемент после скачивания
                        document.body.removeChild(link);
                        window.URL.revokeObjectURL(url); // Освобождаем память
                    }
                } else {
                    console.error('Нет данных в ответе');
                }
            } catch (e) {
                console.error('Ошибка при получении файла:', e.message);
            }
        };

        
        return {
            fileInput,
            uploadedFiles,
            collectionInfo,
            triggerFileInput,
            handleFileUpload,
            createCollection,
            onClose,
            previewFile,
            deleteFile
        };
    },
};

export function hasCollectionForChat(chatId) {
    try {
        const storedCollections = localStorage.getItem('chat_collections');
        if (storedCollections) {
            const collections = JSON.parse(storedCollections);
            return !!collections[chatId];
        }
        return false;
    } catch (error) {
        console.error('Error checking collection existence:', error);
        return false;
    }
}

// Add this as a static method to get collection info
export function getCollectionForChat(chatId) {
    try {
        const storedCollections = localStorage.getItem('chat_collections');
        if (storedCollections) {
            const collections = JSON.parse(storedCollections);
            return collections[chatId] || null;
        }
        return null;
    } catch (error) {
        console.error('Error getting collection info:', error);
        return null;
    }
}
</script>