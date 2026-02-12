<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel PDF merge') }}</title>

        {{-- alpinejs cdn --}}
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.15.8/dist/cdn.min.js"></script>

        {{-- axios cdn --}}
        <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    </head>
    <body>

        <div>
            <div
                x-data="{
                    files: []
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
                <div>
                    <p>รวมไฟล์ PDF</p>

                    <div>
                        <label>
                            <span>เลือกไฟล์ PDF</span>
                            <input 
                                type="file" 
                                multiple 
                                accept=".pdf"
                                @change="files = [...files, ...Array.from($event.target.files)]; $event.target.value = '';"
                            >
                        </label>

                        <div x-show="files.length > 0">
                            <span x-text="`เลือกไฟล์แล้ว ${files.length} ไฟล์`"></span>
                        </div>

                        <button @click="$store.pdfMerge.submit()">รวมไฟล์ PDF</button>

                        <div x-show="$store.pdfMerge.loading">
                            <span>รอสักครู่...</span>
                        </div>
                    </div>

                    <div>
                        <button @click="$store.pdfMerge.preview()">ดูตัวอย่าง</button>
                        <button @click="$store.pdfMerge.download()">ดาวน์โหลด</button>
                    </div>
                </div>
            </div>
        </div>

    </body>
</html>
