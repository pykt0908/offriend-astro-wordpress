import React, { useState, useEffect, useRef, useCallback } from 'react';
import { ChevronLeft, ChevronRight, Play, Pause } from 'lucide-react';

interface Slide {
  id: number;
  image: string;
  alt: string;
  badge?: string;
  title?: string;
  subtitle?: string;
  ctaText?: string;
  ctaLink?: string;
}

interface HeroCarouselProps {
  slides?: Array<{
    image_url?: string;
    kicker?: string;
    title?: string;
    subtitle?: string;
    button_text?: string;
    button_url?: string;
  }>;
}

const DEFAULT_SLIDES: Slide[] = [
  {
    id: 1,
    image: '/images/placeholders/slide-1.svg',
    alt: 'Banner Placeholder 1 (1920 x 800)',
    badge: 'SLIDE 01 / 03',
    title: 'Modern Web Architecture & High-Performance Systems',
    subtitle: 'สถาปัตยกรรมเว็บแอปพลิเคชันและระบบดิจิทัลประสิทธิภาพสูงสำหรับองค์กร',
    ctaText: 'สำรวจบริการ',
    ctaLink: '/services',
  },
  {
    id: 2,
    image: '/images/placeholders/slide-2.svg',
    alt: 'Banner Placeholder 2 (1920 x 800)',
    badge: 'SLIDE 02 / 03',
    title: 'Enterprise Web Applications & Portals',
    subtitle: 'พัฒนาเว็บแอปพลิเคชันและระบบพอร์ทัลองค์กรที่เสถียร ปลอดภัย และขยายตัวได้',
    ctaText: 'ดูผลงานของเรา',
    ctaLink: '/projects',
  },
  {
    id: 3,
    image: '/images/placeholders/slide-3.svg',
    alt: 'Banner Placeholder 3 (1920 x 800)',
    badge: 'SLIDE 03 / 03',
    title: 'Headless WordPress CMS & Astro Velocity',
    subtitle: 'ผสานพลังการบริหารเนื้อหาง่ายดาย เข้ากับความเร็วสูงระดับ Headless',
    ctaText: 'ปรึกษาเรา',
    ctaLink: '/contact',
  },
];

export default function HeroCarousel({ slides: wpSlides }: HeroCarouselProps = {}) {
  // Convert WordPress banners to Slide format, fallback to defaults if empty/missing
  const slides: Slide[] = React.useMemo(() => {
    if (wpSlides && wpSlides.length > 0 && wpSlides.some(s => s.image_url || s.title)) {
      return wpSlides
        .filter(s => s.image_url || s.title)
        .map((s, i) => ({
          id: i + 1,
          image: s.image_url || '/images/placeholders/slide-1.svg',
          alt: s.title || `Banner ${i + 1}`,
          badge: s.kicker || `SLIDE ${String(i + 1).padStart(2, '0')} / ${String(wpSlides.filter(x => x.image_url || x.title).length).padStart(2, '0')}`,
          title: s.title || '',
          subtitle: s.subtitle || '',
          ctaText: s.button_text || '',
          ctaLink: s.button_url || '/',
        }));
    }
    return DEFAULT_SLIDES;
  }, [wpSlides]);


  const [currentIndex, setCurrentIndex] = useState(0);
  const [isPaused, setIsPaused] = useState(false);
  const [touchStartX, setTouchStartX] = useState<number | null>(null);
  const [touchDeltaX, setTouchDeltaX] = useState<number>(0);
  const autoPlayRef = useRef<ReturnType<typeof setInterval> | null>(null);

  const total = slides.length;

  const nextSlide = useCallback(() => {
    setCurrentIndex((prev) => (prev + 1) % total);
  }, [total]);

  const prevSlide = useCallback(() => {
    setCurrentIndex((prev) => (prev - 1 + total) % total);
  }, [total]);

  const goToSlide = (index: number) => {
    setCurrentIndex(index);
  };

  // Autoplay timer
  useEffect(() => {
    if (isPaused) {
      if (autoPlayRef.current) clearInterval(autoPlayRef.current);
      return;
    }

    autoPlayRef.current = setInterval(() => {
      nextSlide();
    }, 5000);

    return () => {
      if (autoPlayRef.current) clearInterval(autoPlayRef.current);
    };
  }, [isPaused, nextSlide]);

  // Touch swipe support
  const handleTouchStart = (e: React.TouchEvent) => {
    setIsPaused(true);
    setTouchStartX(e.touches[0].clientX);
    setTouchDeltaX(0);
  };

  const handleTouchMove = (e: React.TouchEvent) => {
    if (touchStartX === null) return;
    const currentX = e.touches[0].clientX;
    setTouchDeltaX(currentX - touchStartX);
  };

  const handleTouchEnd = () => {
    if (touchStartX !== null) {
      if (touchDeltaX > 50) {
        prevSlide();
      } else if (touchDeltaX < -50) {
        nextSlide();
      }
    }
    setTouchStartX(null);
    setTouchDeltaX(0);
    setIsPaused(false);
  };

  return (
    <div
      className="relative w-full overflow-hidden bg-slate-900 select-none group"
      onMouseEnter={() => setIsPaused(true)}
      onMouseLeave={() => setIsPaused(false)}
      onTouchStart={handleTouchStart}
      onTouchMove={handleTouchMove}
      onTouchEnd={handleTouchEnd}
      aria-label="Hero Banner Carousel (1920x800)"
    >
      {/* 1920x800 Aspect Ratio Container */}
      <div className="relative w-full aspect-[1920/800] min-h-[260px] sm:min-h-[360px] md:min-h-[460px] lg:min-h-[560px] xl:min-h-[660px] 2xl:min-h-[720px]">
        {/* Slides Track */}
        <div
          className="flex h-full w-full transition-transform duration-700 ease-out"
          style={{ transform: `translateX(-${currentIndex * 100}%)` }}
        >
          {slides.map((slide, idx) => (
            <div
              key={slide.id}
              className="relative w-full h-full flex-shrink-0 overflow-hidden"
              aria-hidden={idx !== currentIndex}
            >
              {/* Image banner */}
              <img
                src={slide.image}
                alt={slide.alt}
                className="w-full h-full object-cover object-center pointer-events-none"
                loading={idx === 0 ? 'eager' : 'lazy'}
                decoding="async"
              />

              {/* Bottom Subtle Vignette for Overlay readability */}
              <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/20 pointer-events-none"></div>

              {/* Slide Floating Info Card on bottom-left for premium look */}
              <div className="absolute bottom-6 sm:bottom-12 md:bottom-16 left-4 sm:left-10 md:left-16 max-w-xl z-20 pointer-events-auto">

                <h2 className="text-lg sm:text-2xl md:text-3xl lg:text-4xl font-extrabold text-white leading-tight mb-2 drop-shadow-md">
                  {slide.title}
                </h2>
                <p className="text-xs sm:text-sm md:text-base text-slate-200/90 leading-relaxed mb-4 hidden sm:block max-w-lg drop-shadow">
                  {slide.subtitle}
                </p>
                {slide.ctaText && (
                  <a
                    href={slide.ctaLink}
                    className="inline-flex items-center gap-2 px-4 py-2 sm:px-5 sm:py-2.5 rounded-lg font-bold text-xs sm:text-sm text-white bg-orange-500 hover:bg-orange-600 shadow-md shadow-orange-500/30 transition-all transform hover:-translate-y-0.5 active:scale-95 cursor-pointer"
                  >
                    <span>{slide.ctaText}</span>
                    <ChevronRight className="w-3.5 h-3.5" />
                  </a>
                )}
              </div>
            </div>
          ))}
        </div>


        {/* Left Arrow Button */}
        <button
          type="button"
          onClick={prevSlide}
          className="absolute left-3 sm:left-6 top-1/2 -translate-y-1/2 z-30 w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-black/40 hover:bg-black/70 backdrop-blur-md border border-white/20 text-white flex items-center justify-center transition-all duration-200 opacity-80 group-hover:opacity-100 hover:scale-105 active:scale-95 cursor-pointer focus:outline-none"
          aria-label="Previous Slide"
        >
          <ChevronLeft className="w-5 h-5 sm:w-6 sm:h-6" />
        </button>

        {/* Right Arrow Button */}
        <button
          type="button"
          onClick={nextSlide}
          className="absolute right-3 sm:right-6 top-1/2 -translate-y-1/2 z-30 w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-black/40 hover:bg-black/70 backdrop-blur-md border border-white/20 text-white flex items-center justify-center transition-all duration-200 opacity-80 group-hover:opacity-100 hover:scale-105 active:scale-95 cursor-pointer focus:outline-none"
          aria-label="Next Slide"
        >
          <ChevronRight className="w-5 h-5 sm:w-6 sm:h-6" />
        </button>
      </div>
    </div>
  );
}
