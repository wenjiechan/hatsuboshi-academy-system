window.GakumasMessageAttachments = (() => {
    const maxFiles = 5;
    const maxFileSize = 10 * 1024 * 1024;
    const maxTotalSize = 25 * 1024 * 1024;
    const allowedExtensions = new Set([
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'pdf', 'txt', 'md', 'csv', 'json', 'xml', 'rtf',
        'zip', '7z', 'rar',
        'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'odt', 'ods', 'odp',
    ]);

    const formatFileSize = (bytes) => {
        const size = Number.parseInt(bytes, 10) || 0;

        if (size < 1024) {
            return `${size} B`;
        }

        if (size < 1024 * 1024) {
            return `${(size / 1024).toFixed(1)} KB`;
        }

        return `${(size / (1024 * 1024)).toFixed(1)} MB`;
    };

    const attachmentPreviewText = (message) => {
        const body = String(message?.body || '').replace(/\s+/g, ' ').trim();

        if (body) {
            return body.slice(0, 90);
        }

        const attachments = Array.isArray(message?.attachments) ? message.attachments : [];

        if (attachments.length > 1) {
            return `${attachments.length} attachments`;
        }

        if (attachments.length === 1) {
            const attachment = attachments[0];
            const prefix = attachment.attachment_type === 'image' ? 'Photo: ' : 'File: ';
            return `${prefix}${attachment.original_name || 'Attachment'}`.slice(0, 90);
        }

        return '[No text]';
    };

    const renderMessageAttachments = (bubble, attachments = []) => {
        bubble?.querySelector('[data-message-attachments]')?.remove();

        if (!bubble || !Array.isArray(attachments) || attachments.length === 0) {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'chat-message-attachments';
        wrapper.dataset.messageAttachments = '';

        attachments.forEach((attachment) => {
            const name = attachment.original_name || 'Attachment';

            if (attachment.attachment_type === 'image') {
                const figure = document.createElement('figure');
                figure.className = 'chat-image-attachment';

                const imageLink = document.createElement('a');
                imageLink.href = attachment.url || '#';
                imageLink.target = '_blank';
                imageLink.rel = 'noopener';
                imageLink.setAttribute('aria-label', `Open ${name}`);

                const image = document.createElement('img');
                image.src = attachment.url || '';
                image.alt = name;
                image.loading = 'lazy';
                image.decoding = 'async';
                imageLink.append(image);

                const caption = document.createElement('figcaption');
                const captionName = document.createElement('span');
                captionName.textContent = name;
                captionName.title = name;

                const download = document.createElement('a');
                download.href = attachment.download_url || attachment.url || '#';
                download.setAttribute('aria-label', `Download ${name}`);
                download.innerHTML = '<i class="bi bi-download" aria-hidden="true"></i>';
                caption.append(captionName, download);
                figure.append(imageLink, caption);
                wrapper.append(figure);
                return;
            }

            const fileLink = document.createElement('a');
            fileLink.className = 'chat-file-attachment';
            fileLink.href = attachment.download_url || attachment.url || '#';

            const fileIcon = document.createElement('i');
            fileIcon.className = 'bi bi-file-earmark-text';
            fileIcon.setAttribute('aria-hidden', 'true');

            const copy = document.createElement('span');
            const fileName = document.createElement('strong');
            fileName.textContent = name;
            fileName.title = name;
            const fileSize = document.createElement('small');
            fileSize.textContent = formatFileSize(attachment.file_size);
            copy.append(fileName, fileSize);

            const downloadIcon = document.createElement('i');
            downloadIcon.className = 'bi bi-download';
            downloadIcon.setAttribute('aria-hidden', 'true');
            fileLink.append(fileIcon, copy, downloadIcon);
            wrapper.append(fileLink);
        });

        const body = bubble.querySelector('[data-message-body]');
        body?.after(wrapper);
    };

    const init = ({
        fileInput,
        toggle,
        preview,
        messageSendError,
    }) => {
        let selectedFiles = [];
        let previewUrls = [];

        const showError = (message) => {
            if (!messageSendError) {
                return;
            }

            messageSendError.textContent = message;
            messageSendError.hidden = false;
        };

        const clearError = () => {
            if (!messageSendError) {
                return;
            }

            messageSendError.textContent = '';
            messageSendError.hidden = true;
        };

        const revokePreviewUrls = () => {
            previewUrls.forEach((url) => URL.revokeObjectURL(url));
            previewUrls = [];
        };

        const removeFile = (index) => {
            selectedFiles.splice(index, 1);
            renderSelection();
        };

        const renderSelection = () => {
            revokePreviewUrls();

            if (!preview) {
                return;
            }

            preview.replaceChildren();
            preview.hidden = selectedFiles.length === 0;

            selectedFiles.forEach((file, index) => {
                const item = document.createElement('div');
                const isImage = file.type.startsWith('image/');
                item.className = `message-attachment-preview-item${isImage ? '' : ' file'}`;

                if (isImage) {
                    const imageUrl = URL.createObjectURL(file);
                    previewUrls.push(imageUrl);
                    const image = document.createElement('img');
                    image.src = imageUrl;
                    image.alt = '';
                    item.append(image);
                } else {
                    const icon = document.createElement('i');
                    icon.className = 'bi bi-file-earmark-text';
                    icon.setAttribute('aria-hidden', 'true');
                    item.append(icon);
                }

                const copy = document.createElement('span');
                copy.className = 'message-attachment-preview-copy';
                const name = document.createElement('strong');
                name.textContent = file.name;
                name.title = file.name;
                const size = document.createElement('small');
                size.textContent = formatFileSize(file.size);
                copy.append(name, size);

                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'message-attachment-remove';
                remove.setAttribute('aria-label', `Remove ${file.name}`);
                remove.innerHTML = '<i class="bi bi-x-lg" aria-hidden="true"></i>';
                remove.addEventListener('click', () => removeFile(index));
                item.append(copy, remove);
                preview.append(item);
            });
        };

        const validateFiles = (files) => {
            if (files.length > maxFiles) {
                return `You can attach up to ${maxFiles} files to one message.`;
            }

            let totalSize = 0;

            for (const file of files) {
                const extension = file.name.includes('.')
                    ? file.name.split('.').pop().toLocaleLowerCase()
                    : '';

                if (!allowedExtensions.has(extension)) {
                    return `"${file.name}" is not a supported file type.`;
                }

                if (file.size <= 0) {
                    return `"${file.name}" is empty.`;
                }

                if (file.size > maxFileSize) {
                    return `"${file.name}" is larger than 10 MB.`;
                }

                totalSize += file.size;
            }

            return totalSize > maxTotalSize
                ? 'The selected attachments exceed the 25 MB total limit.'
                : '';
        };

        toggle?.addEventListener('click', () => {
            fileInput.value = '';
            fileInput.click();
        });

        fileInput?.addEventListener('change', () => {
            const incomingFiles = Array.from(fileInput.files || []);
            const fileKeys = new Set(
                selectedFiles.map((file) => `${file.name}:${file.size}:${file.lastModified}`)
            );
            const nextFiles = [...selectedFiles];

            incomingFiles.forEach((file) => {
                const key = `${file.name}:${file.size}:${file.lastModified}`;

                if (!fileKeys.has(key)) {
                    fileKeys.add(key);
                    nextFiles.push(file);
                }
            });

            const validationError = validateFiles(nextFiles);

            if (validationError) {
                showError(validationError);
                fileInput.value = '';
                return;
            }

            selectedFiles = nextFiles;
            clearError();
            fileInput.value = '';
            renderSelection();
        });

        return {
            hasFiles: () => selectedFiles.length > 0,
            clearSelection: () => {
                selectedFiles = [];
                fileInput.value = '';
                renderSelection();
            },
            createFormData: (form) => {
                const formData = new FormData(form);
                formData.delete(fileInput.name);
                selectedFiles.forEach((file) => {
                    formData.append(fileInput.name, file, file.name);
                });
                return formData;
            },
            messagePreview: attachmentPreviewText,
            renderMessageAttachments,
        };
    };

    return {
        init,
        messagePreview: attachmentPreviewText,
        renderMessageAttachments,
    };
})();
