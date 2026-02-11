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

        <div class="min-h-screen bg-gradient-to-br from-sky-50 via-blue-50 to-cyan-50">
            <div
                class="container mx-auto px-4 py-12"
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
                                const formData = new FormData(document.getElementById('form-pdfs'));
                                const url = '{{ route('pdf.merge') }}';

                                const { data, status } = await api.post(url, formData);

                                if (status === 200) {
                                    this.id = data.id;
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
                "
            >
                <div class="max-w-2xl mx-auto">
                    <!-- Header -->
                    <div class="text-center mb-12">
                        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-cyan-500 bg-clip-text text-transparent mb-3">
                            Laravel PDF Merge
                        </h1>
                        <p class="text-slate-600">รวมไฟล์ PDF</p>
                    </div>

                    <!-- Upload Card -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl shadow-blue-100/50 p-8 mb-6">
                        <form id="form-pdfs" enctype="multipart/form-data">
                            <label class="block">
                                <div class="border-2 border-dashed border-blue-300 rounded-2xl p-12 text-center hover:border-blue-400 hover:bg-blue-50/50 transition-all duration-300 cursor-pointer">
                                    <svg class="mx-auto h-16 w-16 text-blue-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <span class="text-lg text-slate-700 font-medium">เลือกไฟล์ PDF</span>
                                    <p class="text-sm text-slate-500 mt-2">หรือลากไฟล์มาวางที่นี่</p>
                                </div>
                                <input name="pdfs[]" type="file" multiple accept="application/pdf" class="hidden">
                            </label>
                        </form>

                        <button 
                            @click="$store.pdfMerge.submit()"
                            class="w-full mt-6 bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white font-semibold py-4 rounded-xl shadow-lg shadow-blue-200 hover:shadow-xl hover:shadow-blue-300 transition-all duration-300 transform hover:scale-[1.02] cursor-pointer"
                        >
                            รวมไฟล์ PDF
                        </button>

                        <div 
                            x-show="$store.pdfMerge.loading"
                            class="mt-6 text-center"
                        >
                            <div class="inline-flex items-center gap-3 bg-blue-50 text-blue-700 px-6 py-3 rounded-full">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="font-medium">รอสักครู่...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-2 gap-4">
                        <button 
                            :disabled="$store.pdfMerge.id === null" 
                            @click="$store.pdfMerge.preview()"
                            :class="$store.pdfMerge.id === null ? 'opacity-50 cursor-not-allowed' : 'hover:scale-[1.02] hover:shadow-lg hover:shadow-sky-200 cursor-pointer'"
                            class="bg-white/80 backdrop-blur-sm text-sky-600 font-semibold py-4 rounded-xl shadow-md border-2 border-sky-200 transition-all duration-300 flex items-center justify-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            ดูตัวอย่าง
                        </button>

                        <button 
                            :disabled="$store.pdfMerge.id === null" 
                            @click="$store.pdfMerge.download()"
                            :class="$store.pdfMerge.id === null ? 'opacity-50 cursor-not-allowed' : 'hover:scale-[1.02] hover:shadow-lg hover:shadow-cyan-200 cursor-pointer'"
                            class="bg-white/80 backdrop-blur-sm text-cyan-600 font-semibold py-4 rounded-xl shadow-md border-2 border-cyan-200 transition-all duration-300 flex items-center justify-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            ดาวน์โหลด
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </body>
</html>
