/**
 * Teacher page shared JavaScript
 */

const ThemeManager = {
  init() {
    document.documentElement.setAttribute('data-theme', 'light');
  }
};

// Animation manager
const AnimationManager = {
  addPageLoadAnimation() {
    const elements = document.querySelectorAll('.card, .stat-card, .main-content, .sidebar');
    elements.forEach((el, index) => {
      el.style.animationDelay = `${index * 0.1}s`;
      el.classList.add('fade-in-up');
    });
  },

  addScrollAnimation() {
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, observerOptions);

    document.querySelectorAll('.stat-card, .function-btn, .report-card, .course-card').forEach(card => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(30px)';
      card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
      observer.observe(card);
    });
  }
};

document.addEventListener('DOMContentLoaded', function() {
  ThemeManager.init();
  AnimationManager.addPageLoadAnimation();
  AnimationManager.addScrollAnimation();
});

window.TeacherCommon = {
  ThemeManager,
  AnimationManager
};
