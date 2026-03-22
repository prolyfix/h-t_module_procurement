import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        uploadUrl:   String,
        submitUrl:   String,
        pdfBaseUrl:  String,
    };

    connect() {
        this.lines = [];
    }

    async uploadDocument(event) {
        event.preventDefault();

        const form = document.getElementById('ocr-upload-form');
        if (!form) return;

        const formData = new FormData(form);

        // Show loading, hide upload section
        document.getElementById('upload-section').classList.add('d-none');
        document.getElementById('loading-section').classList.remove('d-none');
        document.getElementById('results-section').classList.add('d-none');

        try {
            const response = await fetch(this.uploadUrlValue, {
                method: 'POST',
                body: formData,
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Upload failed');
            }

            const data = await response.json();
            this._populateResults(data);
        } catch (err) {
            alert('Error: ' + err.message);
            document.getElementById('upload-section').classList.remove('d-none');
        } finally {
            document.getElementById('loading-section').classList.add('d-none');
        }
    }

    _populateResults(data) {
        // Show PDF in iframe using secure endpoint
        if (data.mediaId) {
            const pdfUrl = this.pdfBaseUrlValue.replace('__ID__', data.mediaId);
            document.getElementById('pdf-viewer').src = pdfUrl;
        }

        // Set document type
        if (data.documentType) {
            const typeSelect = document.getElementById('ocr-document-type');
            if (typeSelect) {
                typeSelect.value = data.documentType;
            }
        }

        // Set document ID
        if (data.documentId) {
            document.getElementById('ocr-document-id').value = data.documentId;
        }

        // Set document date
        if (data.documentDate) {
            document.getElementById('ocr-document-date').value = data.documentDate;
        }

        // Set media ID
        if (data.mediaId) {
            document.getElementById('ocr-media-id').value = data.mediaId;
        }

        // Populate third party select
        const tpSelect = document.getElementById('ocr-third-party-select');
        if (tpSelect && data.knownThirdParties) {
            data.knownThirdParties.forEach(tp => {
                const option = document.createElement('option');
                option.value = tp.id;
                option.textContent = tp.name;
                tpSelect.appendChild(option);
            });
        }

        // Try to match recognized third party name
        if (data.thirdPartyName && tpSelect) {
            const recognizedName = data.thirdPartyName.toLowerCase();
            const hint = document.getElementById('ocr-third-party-recognized');
            let matched = false;

            for (const option of tpSelect.options) {
                if (option.textContent.toLowerCase().includes(recognizedName) ||
                    recognizedName.includes(option.textContent.toLowerCase())) {
                    tpSelect.value = option.value;
                    matched = true;
                    if (hint) hint.textContent = 'Recognized: ' + data.thirdPartyName;
                    break;
                }
            }

            if (!matched) {
                const newTpSection = document.getElementById('new-third-party-section');
                if (newTpSection) newTpSection.style.display = '';
                const newTpHint = document.getElementById('ocr-new-third-party-hint');
                if (newTpHint) newTpHint.textContent = 'Recognized name: ' + data.thirdPartyName;
                const newTpInput = document.getElementById('ocr-new-third-party-name');
                if (newTpInput) newTpInput.value = data.thirdPartyName;
            }
        }

        // Populate related orders
        if (data.relatedOrders && data.relatedOrders.length > 0) {
            const orderSelect = document.getElementById('ocr-related-order');
            if (orderSelect) {
                data.relatedOrders.forEach(order => {
                    const option = document.createElement('option');
                    option.value = order.id;
                    option.textContent = order.orderId;
                    orderSelect.appendChild(option);
                });
                if (data.relatedOrders.length === 1) {
                    orderSelect.value = data.relatedOrders[0].id;
                }
            }
        }

        // Populate lines
        this.lines = data.lines || [];
        this._renderLines();

        // Set total
        if (data.totalAmount !== undefined) {
            document.getElementById('ocr-total').value = data.totalAmount;
        }

        // Show results section
        document.getElementById('results-section').classList.remove('d-none');
    }

    _renderLines() {
        const tbody = document.getElementById('ocr-lines-body');
        if (!tbody) return;
        tbody.innerHTML = '';

        this.lines.forEach((line, index) => {
            const tr = document.createElement('tr');
            tr.dataset.index = index;
            tr.innerHTML = `
                <td><input type="text" class="form-control form-control-sm" value="${this._esc(line.description || '')}" data-field="description" data-index="${index}" aria-label="Description"></td>
                <td><input type="number" class="form-control form-control-sm" value="${line.quantity || ''}" data-field="quantity" data-index="${index}" step="0.01" aria-label="Quantity"></td>
                <td><input type="number" class="form-control form-control-sm" value="${line.grossPrice || ''}" data-field="grossPrice" data-index="${index}" step="0.01" aria-label="Gross price"></td>
                <td><input type="number" class="form-control form-control-sm" value="${line.vat || ''}" data-field="vat" data-index="${index}" aria-label="VAT percent"></td>
                <td><button type="button" class="btn btn-sm btn-danger" data-index="${index}" data-action="click->ocr-scanner#removeLine">&times;</button></td>
            `;
            tr.querySelectorAll('input').forEach(input => {
                input.addEventListener('change', (e) => {
                    const idx = parseInt(e.target.dataset.index);
                    const field = e.target.dataset.field;
                    this.lines[idx][field] = e.target.value;
                });
            });
            tbody.appendChild(tr);
        });
    }

    addLine() {
        this.lines.push({ description: '', quantity: 1, measure: '', grossPrice: 0, netPrice: 0, vat: 19 });
        this._renderLines();
    }

    removeLine(event) {
        const index = parseInt(event.currentTarget.dataset.index);
        this.lines.splice(index, 1);
        this._renderLines();
    }

    async submitDocument() {
        const form = document.getElementById('ocr-result-form');
        if (!form) return;

        const formData = new FormData(form);
        const payload = {
            documentType:     formData.get('documentType'),
            documentId:       formData.get('documentId'),
            documentDate:     formData.get('documentDate'),
            thirdPartyId:     formData.get('thirdPartyId'),
            newThirdPartyName: formData.get('newThirdPartyName'),
            relatedOrderId:   formData.get('relatedOrderId'),
            relatedInvoiceId: formData.get('relatedInvoiceId'),
            mediaId:          formData.get('mediaId'),
            totalAmount:      document.getElementById('ocr-total')?.value || null,
            lines:            this.lines,
        };

        try {
            const response = await fetch(this.submitUrlValue, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Submission failed');
            }

            const data = await response.json();

            document.getElementById('results-section').classList.add('d-none');
            const successSection = document.getElementById('success-section');
            successSection.classList.remove('d-none');
            document.getElementById('success-message').textContent =
                ' ' + data.entityType + ' #' + data.entityId + ' created.';
        } catch (err) {
            alert('Error: ' + err.message);
        }
    }

    _esc(str) {
        return str.replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;');
    }
}
