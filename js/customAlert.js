/**
 * Displays a reusable success alert with an enhanced custom design using SweetAlert2.
 * @param {string} title - The title of the alert (e.g., "Success").
 * @param {string} message - The message of the alert (e.g., "Log In Success").
 */
function showSuccessAlert(title, message) {
    Swal.fire({
        title: title,
        html: `<span class="swal-text-white move-text-up" style="color: white !important;"> ${message}</span>`,
        icon: 'success',
        confirmButtonText: 'OK',
        width: '500px',
        showClass: {
            popup: 'swal-animated-popup'
        },
        customClass: {
            popup: 'swal-custom-popup',
            icon: 'swal-custom-icon',
            title: 'swal-custom-title',
            htmlContainer: 'swal-custom-html',
            confirmButton: 'swal-confirm-button'
        }
    });
}

// Inject CSS styles for the custom alert
const successAlertStyles = document.createElement('style');
successAlertStyles.textContent = `
    /* Base popup styling with high specificity */
    .swal2-container .swal2-popup.swal-custom-popup {
        width: 500px !important;
        border-radius: 20px !important;
        padding: 65px 32px 32px !important;
        background: linear-gradient(to bottom, #8a0000, #5a0000) !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3) !important;
        text-align: center !important;
        position: relative !important;
        border: 1px solid rgba(255, 193, 7, 0.2) !important;
    }
    
    /* Animation for popup entrance */
    .swal-animated-popup {
        animation: popIn 0.4s cubic-bezier(0.26, 0.53, 0.74, 1.48) !important;
    }
    
    @keyframes popIn {
        0% {
            opacity: 0;
            transform: scale(0.8);
        }
        100% {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    /* Success icon styling */
    .swal-custom-icon {
        position: absolute !important;
        left: 50% !important;
        top: 0 !important;
        transform: translate(-50%, -50%) !important;
        margin: 0 !important;
        border: 3px solid #ffc107 !important;
        background-color: #700000 !important;
        box-shadow: 0 0 15px rgba(255, 193, 7, 0.5) !important;
        z-index: 2 !important;
        width: 80px !important;
        height: 80px !important;
    }
    
    .swal-custom-icon.swal2-success [class^=swal2-success-line] {
        background-color: #aaff00 !important;
        height: 3px !important;
    }
    
    .swal-custom-icon.swal2-success .swal2-success-ring {
        border: 0.25em solid rgba(170, 255, 0, 0.3) !important;
    }
    
    .swal-custom-icon.swal2-success [class^=swal2-success-circular-line] {
        background-color: transparent !important;
    }
    
    /* Title styling */
    .swal-custom-title {
        color: #ffc107 !important;
        font-size: 24px !important;
        margin: 10px 0 16px !important;
        font-weight: 600 !important;
        text-shadow: 0 1px 2px rgba(255, 255, 255, 0.2) !important;
    }
    
    /* Message styling */
    .swal-custom-html {
        margin-bottom: 24px !important;
        color: #ffffff !important;
    }
    
    .swal-message {
        color: #ffffff !important;
        font-size: 16px !important;
        opacity: 0.95 !important;
        display: block !important;
        line-height: 1.5 !important;
    }
    
    /* Button styling */
    .swal-confirm-button {
        background: linear-gradient(to bottom, #ffffff, #f0f0f0) !important;
        color: #222222 !important;
        border: none !important;
        border-radius: 8px !important;
        padding: 12px 28px !important;
        font-weight: 600 !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15) !important;
        min-width: 120px !important;
    }
    
    .swal-confirm-button:hover {
        background: linear-gradient(to bottom, #ffd54f, #ffc107) !important;
        color: #700000 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2) !important;
    }
    
    .swal-confirm-button:focus {
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.4) !important;
    }
    
    /* Responsive adjustments */
    @media (max-width: 480px) {
        .swal2-container .swal2-popup.swal-custom-popup {
            width: 90% !important;
            padding: 60px 20px 24px !important;
        }
        
        .swal-custom-title {
            font-size: 20px !important;
        }

        .swal-message {
            font-size: 14px !important;
        }
        
        .swal-custom-icon {
            width: 70px !important;
            height: 70px !important;
        }
    }
`;
document.head.appendChild(successAlertStyles);

// Usage example:
// showSuccessAlert("Success", "Your changes have been saved successfully!");