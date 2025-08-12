/**
 * Template Service plugin for Craft CMS 5.x
 * 
 * @author ByVoss Technologies
 * @copyright Copyright (c) 2025 ByVoss Technologies
 * @link https://byvoss.tech
 * @license MIT
 */

(function() {
    'use strict';

    // Cache for templates
    let templatesCache = null;
    let cacheTimestamp = 0;
    const CACHE_DURATION = 60000; // 1 minute

    // Fetch templates from backend
    async function fetchTemplates() {
        const now = Date.now();
        
        // Use cache if still valid
        if (templatesCache && (now - cacheTimestamp) < CACHE_DURATION) {
            return templatesCache;
        }

        try {
            const response = await fetch(Craft.getCpUrl('template-service/templates'));
            const data = await response.json();
            templatesCache = data.templates;
            cacheTimestamp = now;
            return templatesCache;
        } catch (error) {
            console.error('Failed to fetch templates:', error);
            return [];
        }
    }

    // Create autocomplete dropdown
    function createDropdown(input, templates) {
        // Remove existing dropdown
        removeDropdown(input);

        const dropdown = document.createElement('div');
        dropdown.className = 'template-service-dropdown';
        dropdown.style.position = 'absolute';
        dropdown.style.zIndex = '10000'; // Higher than Craft's dropdowns
        
        // Position dropdown below input
        const rect = input.getBoundingClientRect();
        dropdown.style.top = (rect.bottom + window.scrollY) + 'px';
        dropdown.style.left = rect.left + 'px';
        dropdown.style.width = rect.width + 'px';

        // Filter templates based on input value
        const query = input.value.toLowerCase();
        const filtered = templates.filter(t => 
            t.path.toLowerCase().includes(query)
        );

        // Create dropdown items
        filtered.slice(0, 20).forEach(template => {
            const item = document.createElement('div');
            item.className = 'template-service-item';
            item.dataset.path = template.path;
            
            // Add icon based on type
            const icon = template.type === 'folder' ? '📁' : '📄';
            item.innerHTML = `<span class="template-icon">${icon}</span> ${template.label}`;
            
            // Click handler
            item.addEventListener('click', function() {
                input.value = template.path;
                input.dispatchEvent(new Event('change', { bubbles: true }));
                removeDropdown(input);
            });

            dropdown.appendChild(item);
        });

        // Add "no results" message if needed
        if (filtered.length === 0) {
            const noResults = document.createElement('div');
            noResults.className = 'template-service-no-results';
            noResults.textContent = 'No templates found';
            dropdown.appendChild(noResults);
        }

        // Attach to body
        document.body.appendChild(dropdown);
        input.setAttribute('data-template-dropdown', 'active');
    }

    // Remove dropdown
    function removeDropdown(input) {
        const existing = document.querySelector('.template-service-dropdown');
        if (existing) {
            existing.remove();
        }
        input.removeAttribute('data-template-dropdown');
    }

    // Initialize autocomplete on an input
    async function initAutocomplete(input) {
        // Skip if already initialized
        if (input.hasAttribute('data-template-autocomplete')) {
            return;
        }
        
        input.setAttribute('data-template-autocomplete', 'true');
        
        // Focus handler
        input.addEventListener('focus', async function() {
            const templates = await fetchTemplates();
            createDropdown(input, templates);
        });

        // Input handler for filtering
        input.addEventListener('input', async function() {
            const templates = await fetchTemplates();
            createDropdown(input, templates);
        });

        // Blur handler
        input.addEventListener('blur', function(e) {
            // Delay to allow click on dropdown items
            setTimeout(() => {
                if (!input.hasAttribute('data-template-dropdown-hover')) {
                    removeDropdown(input);
                }
            }, 200);
        });

        // Keyboard navigation
        input.addEventListener('keydown', function(e) {
            const dropdown = document.querySelector('.template-service-dropdown');
            if (!dropdown) return;

            const items = dropdown.querySelectorAll('.template-service-item');
            const active = dropdown.querySelector('.template-service-item.active');
            let index = Array.from(items).indexOf(active);

            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    index = (index + 1) % items.length;
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    index = index <= 0 ? items.length - 1 : index - 1;
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (active) {
                        input.value = active.dataset.path;
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                        removeDropdown(input);
                    }
                    return;
                case 'Escape':
                    removeDropdown(input);
                    return;
                default:
                    return;
            }

            // Update active item
            items.forEach(item => item.classList.remove('active'));
            if (items[index]) {
                items[index].classList.add('active');
                items[index].scrollIntoView({ block: 'nearest' });
            }
        });
    }

    // Find and initialize all template inputs
    function findAndInitInputs() {
        // Only target specific template fields, not all inputs with "template" in name
        const selectors = [
            'input[name="settings[template]"]', // Site settings template field
            'input[name="entryType[template]"]', // Entry type template
            'input[name="section[template]"]', // Section template
            'input[name="category[template]"]', // Category template
            'input[name="settings[defaultTemplate]"]' // Default template field
        ];

        selectors.forEach(selector => {
            const inputs = document.querySelectorAll(selector);
            inputs.forEach(input => {
                // Check if Craft 5's own autocomplete is present
                const hasNativeAutocomplete = input.closest('.autosuggest-container') || 
                                             input.hasAttribute('data-autosuggest');
                
                // Only init if no native autocomplete and not already initialized
                if (!hasNativeAutocomplete && input.type === 'text' && !input.hasAttribute('data-template-autocomplete')) {
                    console.log('Template Service: Initializing for', input.name);
                    initAutocomplete(input);
                }
            });
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', findAndInitInputs);
    } else {
        findAndInitInputs();
    }

    // Re-scan when Craft adds new fields (e.g., Matrix blocks)
    if (window.Garnish) {
        const originalAddItem = Garnish.Base.prototype.addItems;
        Garnish.Base.prototype.addItems = function() {
            const result = originalAddItem.apply(this, arguments);
            setTimeout(findAndInitInputs, 100);
            return result;
        };
    }

    // Observe DOM for dynamically added inputs
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                setTimeout(findAndInitInputs, 100);
            }
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
})();