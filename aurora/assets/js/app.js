/**
 * Aurora Theater - Client-side JS Logic
 * 
 * Beheert mobiel menu, flash meldingen en interactieve ticket boeking.
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. MOBIEL MENU TOGGLE
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const navMenu = document.getElementById('nav-menu');
    
    if (mobileMenuBtn && navMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            mobileMenuBtn.classList.toggle('active');
            
            // Hamburger animatie
            const bars = mobileMenuBtn.querySelectorAll('.bar');
            if (mobileMenuBtn.classList.contains('active')) {
                bars[0].style.transform = 'rotate(-45deg) translate(-5px, 6px)';
                bars[1].style.opacity = '0';
                bars[2].style.transform = 'rotate(45deg) translate(-5px, -6px)';
            } else {
                bars[0].style.transform = 'none';
                bars[1].style.opacity = '1';
                bars[2].style.transform = 'none';
            }
        });
    }

    // 2. FLASH MELDING AUTOMATISCH VERDWIJNEN (Happy/Unhappy Alert fading)
    const flashAlert = document.getElementById('flash-alert');
    if (flashAlert) {
        // Verberg na 5 seconden met een zachte fade-out
        setTimeout(function() {
            flashAlert.style.transition = 'opacity 0.5s ease';
            flashAlert.style.opacity = '0';
            setTimeout(function() {
                flashAlert.style.display = 'none';
            }, 500);
        }, 5000);
    }

    // 3. INTERACTIEVE STOEL SELECTIE (Tickets boeken)
    const seatMap = document.getElementById('interactive-seat-map');
    if (seatMap) {
        const seats = seatMap.querySelectorAll('.seat.available');
        const selectedSeatsInput = document.getElementById('selected-seats-input');
        const ticketsCountInput = document.getElementById('tickets-count-input');
        const summarySeats = document.getElementById('summary-seats');
        const summaryCount = document.getElementById('summary-count');
        const summarySubtotal = document.getElementById('summary-subtotal');
        const summaryFee = document.getElementById('summary-fee');
        const summaryTotal = document.getElementById('summary-total');
        const submitBookingBtn = document.getElementById('submit-booking-btn');
        
        // Prijs per ticket en servicekosten ophalen uit data-attributen van seatMap
        const pricePerTicket = parseFloat(seatMap.dataset.price);
        const serviceFee = parseFloat(seatMap.dataset.fee);
        
        let selectedSeats = [];
        
        seats.forEach(seat => {
            seat.addEventListener('click', function() {
                const seatNumber = this.dataset.seat;
                
                if (this.classList.contains('selected')) {
                    // Deselecteren
                    this.classList.remove('selected');
                    selectedSeats = selectedSeats.filter(s => s !== seatNumber);
                } else {
                    // Selecteren
                    this.classList.add('selected');
                    selectedSeats.push(seatNumber);
                }
                
                // Update UI en formulier invoeren
                updateBookingSummary();
            });
        });
        
        function updateBookingSummary() {
            const count = selectedSeats.length;
            
            // Sorteer stoelnummers alfabetisch/numeriek voor nette weergave
            selectedSeats.sort();
            
            // Update verborgen velden voor formulierverzending
            selectedSeatsInput.value = selectedSeats.join(', ');
            ticketsCountInput.value = count;
            
            // Update samenvatting in HTML
            if (count > 0) {
                summarySeats.textContent = selectedSeats.join(', ');
                summaryCount.textContent = count + 'x';
                
                const subtotal = count * pricePerTicket;
                const total = subtotal + serviceFee;
                
                summarySubtotal.textContent = '€ ' + subtotal.toFixed(2).replace('.', ',');
                summaryFee.textContent = '€ ' + serviceFee.toFixed(2).replace('.', ',');
                summaryTotal.textContent = '€ ' + total.toFixed(2).replace('.', ',');
                
                // Schakel de boekingsknop in
                submitBookingBtn.disabled = false;
                submitBookingBtn.classList.remove('disabled');
            } else {
                summarySeats.textContent = 'Geen stoelen geselecteerd';
                summaryCount.textContent = '0x';
                summarySubtotal.textContent = '€ 0,00';
                summaryFee.textContent = '€ 0,00';
                summaryTotal.textContent = '€ 0,00';
                
                // Schakel de boekingsknop uit
                submitBookingBtn.disabled = true;
                submitBookingBtn.classList.add('disabled');
            }
        }
    }
});
