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
                <input type="file" class="hidden" multiple />
            </div>
        </div>
    </div>
</template>

<script>
import { X, Upload } from "lucide-vue-next";

export default {
    name: "FileUpload",
    props: {
        onClose: {
            type: Function,
            required: true,
        },
    },
    components: {
        X,
        Upload,
    },
    setup(props) {
        const onClose = () => {
            props.onClose();
        };

        return {
            onClose,
        };
    },
};
</script>
