<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel PDF merge') }}</title>

        <style>
            body {
                padding: 0;
                margin: 0;
            }
        </style>

        {{-- alpinejs cdn --}}
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.15.8/dist/cdn.min.js"></script>

        {{-- axios cdn --}}
        <script src="https://unpkg.com/axios/dist/axios.min.js"></script>

        {{-- tailwindcss cdn --}}
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    </head>
    <body>

        <div class="min-h-screen max-h-screen overflow-y-auto bg-gradient-to-br from-sky-50 via-blue-50 to-cyan-50">
            <div
                class="container mx-auto px-4 py-6"
                x-data="{
                    files: [],
                    draggedIndex: null,
                    dragoverIndex: null,
                    originalFilesCount: 0
                }"
                x-init="
                    const api = axios.create({
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    Alpine.store('pdfMerge', {
                        id: null,
                        loading: false,

                        async submit() {
                            this.loading = true;

                            try {
                                const formData = new FormData();

                                // Add files in order
                                const filesArray = Alpine.raw($data.files);
                                filesArray.forEach(file => {
                                    formData.append('pdfs[]', file);
                                });

                                const url = '{{ route('pdf.merge') }}';

                                const { data, status } = await api.post(url, formData);

                                if (status === 200) {
                                    this.id = data.id;
                                    $data.originalFilesCount = $data.files.length;
                                }
                            }
                            catch (error) {
                                console.error(error);
                            }
                            finally {
                                this.loading = false;
                            }
                        },

                        async preview() {
                            const url = '{{ route('pdf.preview') }}';
                            window.open(`${url}?id=${this.id}`);
                        },

                        async download() {
                            const url = '{{ route('pdf.download') }}';
                            window.open(`${url}?id=${this.id}`);
                        }
                    });

                    $watch('files', () => {
                        if ($data.files.length === 0) {
                            Alpine.store('pdfMerge').id = null;
                            $data.originalFilesCount = 0;
                        } 
                        else if (Alpine.store('pdfMerge').id !== null && $data.files.length !== $data.originalFilesCount) {
                            Alpine.store('pdfMerge').id = null;
                        }
                    });
                "
            >
                <div class="max-w-2xl mx-auto">
                    {{-- Header --}}
                    <div class="text-center mb-6">
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-cyan-500 bg-clip-text text-transparent mb-2">
                            Laravel PDF Merge
                        </h1>
                        <p class="text-sm text-slate-600">รวมไฟล์ PDF</p>
                    </div>

                    {{-- Upload Card --}}
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl shadow-blue-100/50 p-6 mb-4">
                        <label class="block">
                            <div class="border-2 border-dashed border-blue-300 rounded-xl p-8 text-center hover:border-blue-400 hover:bg-blue-50/50 transition-all duration-300 cursor-pointer">
                                <svg class="mx-auto h-12 w-12 text-blue-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <span class="text-base text-slate-700 font-medium">เลือกไฟล์ PDF</span>
                            </div>
                            <input 
                                type="file" 
                                multiple 
                                accept=".pdf" 
                                class="hidden"
                                @change="files = [...files, ...Array.from($event.target.files)]; $event.target.value = '';"
                            >
                        </label>

                        {{-- File List with Drag and Drop --}}
                        <div x-show="files.length > 0" class="mt-4 space-y-2 max-h-84 overflow-y-auto">
                            <div class="flex items-center justify-between mb-2 sticky top-0 bg-white/95 backdrop-blur-sm py-2 z-10">
                                <h3 class="text-xs font-semibold text-slate-700">ไฟล์ที่เลือก (ลากเพื่อเรียงลำดับ)</h3>
                                <button 
                                    @click="files = []; $store.pdfMerge.id = null;"
                                    class="text-xs text-red-500 hover:text-red-600 font-medium transition-colors cursor-pointer"
                                >
                                    ล้างทั้งหมด
                                </button>
                            </div>

                            <template x-for="(file, index) in files" :key="index">
                                <div 
                                    draggable="true"
                                    @dragstart="draggedIndex = index"
                                    @dragend="draggedIndex = null; dragoverIndex = null"
                                    @dragover.prevent="dragoverIndex = index"
                                    @drop.prevent="
                                        if (draggedIndex !== null && draggedIndex !== index) {
                                            const newFiles = [...files];
                                            const draggedFile = newFiles[draggedIndex];
                                            newFiles.splice(draggedIndex, 1);
                                            newFiles.splice(index, 0, draggedFile);
                                            files = newFiles;
                                        }
                                        draggedIndex = null;
                                        dragoverIndex = null;
                                    "
                                    :class="{
                                        'opacity-50': draggedIndex === index,
                                        'border-t-2 border-blue-400': dragoverIndex === index && draggedIndex !== index
                                    }"
                                    class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg p-3 flex items-center gap-3 cursor-move hover:shadow-md transition-all duration-200 border-2 border-transparent"
                                >
                                    {{-- Drag Handle --}}
                                    <div class="flex-shrink-0 text-blue-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                        </svg>
                                    </div>

                                    {{-- File Icon --}}
                                    <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-red-500 to-pink-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>

                                    {{-- File Info --}}
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-medium text-slate-700 truncate" x-text="file.name"></p>
                                        <p class="text-xs text-slate-500" x-text="(file.size / 1024).toFixed(2) + ' KB'"></p>
                                    </div>

                                    {{-- Order Number --}}
                                    <div class="flex-shrink-0 w-7 h-7 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-full flex items-center justify-center">
                                        <span class="text-white text-xs font-bold" x-text="index + 1"></span>
                                    </div>

                                    {{-- Remove Button --}}
                                    <button 
                                        @click.stop="files = files.filter((_, i) => i !== index); if (files.length === 0) { $store.pdfMerge.id = null; }"
                                        class="flex-shrink-0 w-7 h-7 rounded-full bg-red-100 hover:bg-red-200 text-red-600 flex items-center justify-center transition-colors cursor-pointer"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <button
                            @click="$store.pdfMerge.submit()"
                            :disabled="files.length < 2 || ($store.pdfMerge.id !== null && files.length !== originalFilesCount)"
                            :class="files.length < 2 || ($store.pdfMerge.id !== null && files.length !== originalFilesCount) ? 'opacity-50 cursor-not-allowed' : 'hover:from-blue-600 hover:to-cyan-600 hover:shadow-xl hover:shadow-blue-300 hover:scale-[1.02] cursor-pointer'"
                            class="w-full mt-4 bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-semibold py-3 rounded-xl shadow-lg shadow-blue-200 transition-all duration-300 transform"
                        >
                            รวมไฟล์ PDF
                        </button>

                        <div
                            x-show="$store.pdfMerge.loading"
                            class="mt-4 text-center"
                        >
                            <div class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-4 py-2 rounded-full">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="font-medium text-sm">รอสักครู่...</span>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            :disabled="$store.pdfMerge.id === null || files.length === 0 || files.length !== originalFilesCount"
                            @click="$store.pdfMerge.preview()"
                            :class="$store.pdfMerge.id === null || files.length === 0 || files.length !== originalFilesCount ? 'opacity-50 cursor-not-allowed' : 'hover:scale-[1.02] hover:shadow-lg hover:shadow-sky-200 cursor-pointer'"
                            class="bg-white/80 backdrop-blur-sm text-sky-600 font-semibold py-3 rounded-xl shadow-md border-2 border-sky-200 transition-all duration-300 flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span class="text-sm">ดูตัวอย่าง</span>
                        </button>

                        <button
                            :disabled="$store.pdfMerge.id === null || files.length === 0 || files.length !== originalFilesCount"
                            @click="$store.pdfMerge.download()"
                            :class="$store.pdfMerge.id === null || files.length === 0 || files.length !== originalFilesCount ? 'opacity-50 cursor-not-allowed' : 'hover:scale-[1.02] hover:shadow-lg hover:shadow-cyan-200 cursor-pointer'"
                            class="bg-white/80 backdrop-blur-sm text-cyan-600 font-semibold py-3 rounded-xl shadow-md border-2 border-cyan-200 transition-all duration-300 flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span class="text-sm">ดาวน์โหลด</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </body>
</html>
