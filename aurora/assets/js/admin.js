/**
 * Aurora Theater - Admin Panel JS Logic
 * 
 * Beheert sidebar toggles, deletie-bevestigingen en tabel zoekfilters.
 */

document.addEventListener('DOMContentLoaded', function() {

    // 1. COLLAPSIBLE SIDEBAR VOOR MOBIEL
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const adminSidebar = document.getElementById('admin-sidebar');
    
    if (sidebarToggle && adminSidebar) {
        // Maak overlay aan indien niet aanwezig
        let overlay = document.querySelector('.sidebar-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);
        }
        
        sidebarToggle.addEventListener('click', function() {
            adminSidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        });
        
        overlay.addEventListener('click', function() {
            adminSidebar.classList.remove('open');
            overlay.classList.remove('active');
        });
    }

    // 2. BEVESTIGING BIJ DELETE ACTIES
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const confirmMsg = this.dataset.confirm || "Weet u zeker dat u dit item wilt verwijderen? Dit kan niet ongedaan worden gemaakt.";
            if (!confirm(confirmMsg)) {
                e.preventDefault(); // Annuleer de klik / form verzending
            }
        });
    });

    // 3. LIVE TABLE ZOEKFILTER (Client-side live filter)
    const tableSearch = document.getElementById('table-search');
    const adminTable = document.querySelector('.admin-table');
    
    if (tableSearch && adminTable) {
        tableSearch.addEventListener('keyup', function() {
            const query = this.value.toLowerCase();
            const rows = adminTable.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                let match = false;
                const cells = row.querySelectorAll('td');
                
                cells.forEach(cell => {
                    // Sla actieknoppen over bij het doorzoeken van velden
                    if (!cell.classList.contains('action-buttons')) {
                        if (cell.textContent.toLowerCase().includes(query)) {
                            match = true;
                        }
                    }
                });
                
                if (match) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Toon lege melding als alle rijen verborgen zijn
            const visibleRows = Array.from(rows).filter(r => r.style.display !== 'none');
            let noResultsRow = document.getElementById('no-search-results-row');
            
            if (visibleRows.length === 0) {
                if (!noResultsRow) {
                    const colCount = adminTable.querySelectorAll('thead th').length;
                    noResultsRow = document.createElement('tr');
                    noResultsRow.id = 'no-search-results-row';
                    noResultsRow.innerHTML = `<td colspan="${colCount}" class="text-center" style="color: var(--admin-text-muted); padding: 30px 0;">Geen overeenkomende resultaten gevonden.</td>`;
                    adminTable.querySelector('tbody').appendChild(noResultsRow);
                }
            } else {
                if (noResultsRow) {
                    noResultsRow.remove();
                }
            }
        });
    }

    // 4. AFBEELDING PREVIEW BIJ SHOW UPLOAD
    const showImageInput = document.getElementById('afbeelding-input');
    const showImagePreview = document.getElementById('afbeelding-preview');
    
    if (showImageInput && showImagePreview) {
        showImageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    showImagePreview.src = e.target.result;
                    showImagePreview.parentElement.style.display = 'flex';
                }
                reader.readAsDataURL(file);
            }
        });
    }
});
