<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="w-96 rounded-lg shadow-lg p-6 bg-secondary">
            <div class="flex items-center justify-between mb-4">
                <h2 class="`text-xl font-semibold text-secondary`">
                    Upload Files
                </h2>
                <button
                    @click="onClose"
                    class="p-1 rounded-lg hover:bg-secondary text-primary"
                >
                    <X size="20" />
                </button>
            </div>

            <div class="border-2 border-dashed rounded-lg p-8 text-center border-secondary">
                <Upload
                    :class="`mx-auto mb-4 text-primary`"
                    size="32"
                />
                <p class="text-primary">
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
