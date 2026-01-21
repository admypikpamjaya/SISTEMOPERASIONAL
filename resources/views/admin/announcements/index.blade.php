@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1" style="color: #1a1a1a;">Pengumuman</h1>
            <p class="text-muted mb-0" style="font-size: 0.95rem;">Sistem Operasional Yayasan</p>
        </div>
        
        <!-- Search Bar -->
        <div style="width: 590px;">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0" style="border-radius: 8px 0 0 8px;">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" class="form-control border-start-0 ps-3" placeholder="Cari pengumuman..." 
                       style="border-radius: 0 8px 8px 0; border: 1px solid #ddd; font-size: 0.95rem;">
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 8px; border: none;">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Kolom Buat Pengumuman -->
        <div class="col-xl-6">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white border-0 py-3" style="border-radius: 12px 12px 0 0;">
                    <h5 class="fw-semibold mb-0" style="color: #1a1a1a; font-size: 1.1rem;">Buat Pengumuman</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="/admin/announcements" id="announcementForm">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-medium mb-2" style="font-size: 0.9rem; color: #333;">Judul pengumuman</label>
                            <input
                                type="text"
                                name="title"
                                id="title"
                                class="form-control"
                                style="border-radius: 8px; border: 1px solid #ddd; padding: 10px 14px; font-size: 0.95rem;"
                                placeholder="Masukkan judul pengumuman..."
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium mb-2" style="font-size: 0.9rem; color: #333;">Isi pengumuman</label>
                            <textarea
                                name="message"
                                id="message"
                                class="form-control"
                                rows="4"
                                style="border-radius: 8px; border: 1px solid #ddd; padding: 12px 14px; font-size: 0.95rem; resize: vertical;"
                                placeholder="Tulis isi pengumuman di sini..."
                                required
                            ></textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium mb-2" style="font-size: 0.9rem; color: #333;">Prioritas</label>
                                <select name="priority" id="priority" class="form-select" style="border-radius: 8px; border: 1px solid #ddd; padding: 10px 14px; font-size: 0.95rem;" required>
                                    <option value="low">Rendah</option>
                                    <option value="medium" selected>Sedang</option>
                                    <option value="high">Tinggi</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-medium mb-2" style="font-size: 0.9rem; color: #333;">Departemen</label>
                                <select name="department" id="department" class="form-select" style="border-radius: 8px; border: 1px solid #ddd; padding: 10px 14px; font-size: 0.95rem;" required>
                                    <option value="sekretariat">Sekretariat</option>
                                    <option value="keuangan">Divisi Keuangan</option>
                                    <option value="it">IT Support</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 pt-2">
                            <button type="submit" class="btn w-100 py-2 fw-semibold" style="background-color: #1e88e5; color: white; border: none; border-radius: 8px; font-size: 0.95rem;">
                                Kirim Pengumuman
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Daftar Pengumuman -->
        <div class="col-xl-6">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-3">
                    <!-- Border radius diubah menjadi 20px -->
                    <div class="px-3 py-2" style="background-color: #1e88e5; border-radius: 10px;">
                        <h5 class="mb-0 fw-semibold text-white" style="font-size: 1.1rem;">Daftar Pengumuman</h5>
                    </div>
                    <span class="badge bg-light text-dark px-3 py-2 fw-medium" style="border-radius: 8px; font-size: 0.85rem;">
                        <span id="announcementCount">{{ count($announcements ?? []) }}</span> pengumuman
                    </span>
                </div>
            </div>

            <!-- Daftar Pengumuman -->
            <div class="announcement-list" style="max-height: 600px; overflow-y: auto;">
                @forelse($announcements ?? [] as $announcement)
                <div class="card mb-3 border-0 shadow-sm announcement-card" 
                    style="border-radius: 12px; 
                           @if($announcement->priority == 'high') border-left: 4px solid #dc3545 !important;
                           @elseif($announcement->priority == 'medium') border-left: 4px solid #ffc107 !important;
                           @else border-left: 4px solid #28a745 !important; @endif">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title mb-0 fw-semibold" style="color: #1a1a1a; font-size: 1.05rem;">{{ $announcement->title }}</h5>
                            <span class="badge px-3 py-2 fw-medium" 
                                style="border-radius: 6px; font-size: 0.8rem;
                                       @if($announcement->priority == 'high') background-color: rgba(220, 53, 69, 0.1); color: #dc3545;
                                       @elseif($announcement->priority == 'medium') background-color: rgba(255, 193, 7, 0.1); color: #856404;
                                       @else background-color: rgba(40, 167, 69, 0.1); color: #155724; @endif">
                                @if($announcement->priority == 'high')
                                    Tinggi
                                @elseif($announcement->priority == 'medium')
                                    Sedang
                                @else
                                    Rendah
                                @endif
                            </span>
                        </div>
                        
                        <p class="card-text mb-3" style="color: #555; font-size: 0.95rem; line-height: 1.6;">
                            {{ $announcement->message }}
                        </p>
                        
                        <div class="d-flex justify-content-between align-items-center mt-4 pt-2" style="border-top: 1px solid #eee;">
                            <div class="d-flex align-items-center gap-2" style="color: #666; font-size: 0.85rem;">
                                <i class="fas fa-building" style="color: #1e88e5;"></i>
                                <span class="fw-medium">
                                    @if($announcement->department == 'sekretariat')
                                        Sekretariat
                                    @elseif($announcement->department == 'keuangan')
                                        Divisi Keuangan
                                    @else
                                        IT Support
                                    @endif
                                </span>
                            </div>
                            <div style="color: #888; font-size: 0.85rem;">
                                <i class="far fa-clock me-1"></i> <span class="time-ago">{{ $announcement->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body text-center py-5">
                        <div class="mb-3" style="color: #ccc;">
                            <i class="fas fa-bullhorn" style="font-size: 3rem;"></i>
                        </div>
                        <h6 class="fw-medium mb-2" style="color: #666;">Belum ada pengumuman</h6>
                        <p class="text-muted mb-0" style="font-size: 0.9rem;">Pengumuman yang dibuat akan muncul di sini</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Font Awesome untuk ikon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Bootstrap JS untuk alert dismissible -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    body {
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        background-color: #f8f9fa;
    }
    
    .input-group {
        border-radius: 8px;
        overflow: hidden;
    }
    
    .input-group-text {
        border-right: none !important;
        padding: 0.6rem 0.875rem;
    }
    
    .form-control:focus {
        border-color: #1e88e5;
        box-shadow: 0 0 0 0.25rem rgba(30, 136, 229, 0.15);
    }
    
    .btn-primary {
        background-color: #1e88e5;
        border-color: #1e88e5;
    }
    
    .btn-primary:hover {
        background-color: #1976d2;
        border-color: #1976d2;
    }
    
    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08) !important;
    }
    
    /* Scrollbar styling */
    .announcement-list::-webkit-scrollbar {
        width: 6px;
    }
    
    .announcement-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .announcement-list::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }
    
    .announcement-list::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality
        const searchInput = document.querySelector('input[placeholder="Cari pengumuman..."]');
        const announcementCards = document.querySelectorAll('.announcement-list .announcement-card');
        
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            
            announcementCards.forEach(card => {
                const title = card.querySelector('.card-title').textContent.toLowerCase();
                const content = card.querySelector('.card-text').textContent.toLowerCase();
                const department = card.querySelector('.fw-medium').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || content.includes(searchTerm) || department.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Form submission for adding new announcement
        const announcementForm = document.getElementById('announcementForm');
        const announcementList = document.querySelector('.announcement-list');
        const announcementCount = document.getElementById('announcementCount');
        
        announcementForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            const title = formData.get('title');
            const message = formData.get('message');
            const priority = formData.get('priority');
            const department = formData.get('department');
            
            // Create new announcement card
            const newAnnouncement = document.createElement('div');
            newAnnouncement.className = 'card mb-3 border-0 shadow-sm announcement-card';
            newAnnouncement.style.borderRadius = '12px';
            
            // Set border color based on priority
            if (priority === 'high') {
                newAnnouncement.style.borderLeft = '4px solid #dc3545 !important';
            } else if (priority === 'medium') {
                newAnnouncement.style.borderLeft = '4px solid #ffc107 !important';
            } else {
                newAnnouncement.style.borderLeft = '4px solid #28a745 !important';
            }
            
            // Set badge color based on priority
            let priorityText = '';
            let badgeClass = '';
            if (priority === 'high') {
                priorityText = 'Tinggi';
                badgeClass = 'background-color: rgba(220, 53, 69, 0.1); color: #dc3545;';
            } else if (priority === 'medium') {
                priorityText = 'Sedang';
                badgeClass = 'background-color: rgba(255, 193, 7, 0.1); color: #856404;';
            } else {
                priorityText = 'Rendah';
                badgeClass = 'background-color: rgba(40, 167, 69, 0.1); color: #155724;';
            }
            
            // Set department text
            let departmentText = '';
            if (department === 'sekretariat') {
                departmentText = 'Sekretariat';
            } else if (department === 'keuangan') {
                departmentText = 'Divisi Keuangan';
            } else {
                departmentText = 'IT Support';
            }
            
            // HTML content for new announcement
            newAnnouncement.innerHTML = `
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title mb-0 fw-semibold" style="color: #1a1a1a; font-size: 1.05rem;">${title}</h5>
                        <span class="badge px-3 py-2 fw-medium" style="border-radius: 6px; font-size: 0.8rem; ${badgeClass}">
                            ${priorityText}
                        </span>
                    </div>
                    
                    <p class="card-text mb-3" style="color: #555; font-size: 0.95rem; line-height: 1.6;">
                        ${message}
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-2" style="border-top: 1px solid #eee;">
                        <div class="d-flex align-items-center gap-2" style="color: #666; font-size: 0.85rem;">
                            <i class="fas fa-building" style="color: #1e88e5;"></i>
                            <span class="fw-medium">${departmentText}</span>
                        </div>
                        <div style="color: #888; font-size: 0.85rem;">
                            <i class="far fa-clock me-1"></i> <span class="time-ago">Baru saja</span>
                        </div>
                    </div>
                </div>
            `;
            
            // Insert at the top of the list
            const firstCard = announcementList.querySelector('.announcement-card');
            const emptyState = announcementList.querySelector('.card-body.text-center');
            
            // Remove empty state if it exists
            if (emptyState && emptyState.closest('.card')) {
                emptyState.closest('.card').remove();
            }
            
            if (firstCard) {
                announcementList.insertBefore(newAnnouncement, firstCard);
            } else {
                announcementList.appendChild(newAnnouncement);
            }
            
            // Update announcement count
            const currentCount = parseInt(announcementCount.textContent);
            announcementCount.textContent = currentCount + 1;
            
            // Reset form
            announcementForm.reset();
            
            // Set default values
            document.getElementById('priority').value = 'medium';
            document.getElementById('department').value = 'sekretariat';
            
            // Show success message
            const successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success alert-dismissible fade show';
            successAlert.style.borderRadius = '8px';
            successAlert.style.border = 'none';
            successAlert.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <span>Pengumuman berhasil dibuat!</span>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            const container = document.querySelector('.container-fluid');
            const header = container.querySelector('.d-flex.justify-content-between');
            header.parentNode.insertBefore(successAlert, header.nextSibling);
            
            // Auto remove alert after 5 seconds
            setTimeout(() => {
                if (successAlert.parentNode) {
                    successAlert.remove();
                }
            }, 5000);
        });
    });
</script>
@endsection