
$(document).ready(function () {



    // gsap.registerPlugin(ScrollSmoother);
    gsap.registerPlugin(ScrollTrigger);

    // create the smooth scroller FIRST!
    // let smoother = ScrollSmoother.create({
    //     smooth: 0.9,
    //     effects: true
    // });
    ScrollTrigger.saveStyles(".line-rotate-anim")
    var line_rotates = document.querySelectorAll(".line-rotate-anim, h2");

    line_rotates.forEach(line_rotate => {
        let line_rotate_txt = new SplitText(line_rotate, { type: "lines" });
        gsap.set(line_rotate, { perspective: 400 });
        let tl = gsap.timeline({
            defaults: { duration: 1 },
            scrollTrigger: {
                trigger: line_rotate,
            }
        }).fromTo(line_rotate_txt.lines, {
            y: "60%",
            rotateX: "-40deg",
            opacity: 0
        }, {
            y: "0%",
            rotateX: 0,
            opacity: 1,
            duration: 1,
            stagger: .13,
            ease: "power3.out",
            delay: .2
        })
    });

    var heading_anim = document.querySelector(".line-rotate-heading, h1");
    let heading_anim_txt = new SplitText(heading_anim, { type: "lines" });
    gsap.set(heading_anim, { perspective: 400 });
    let tl = gsap.timeline({
        defaults: { duration: 1 },
        scrollTrigger: {
            trigger: heading_anim,
            // scrub: true,
            // start: "center 50%",
            // end: "bottom -50%",
            // pin: true
        }
    }).fromTo(heading_anim_txt.lines, {
        y: "60%",
        rotateX: "-40deg",
        opacity: 0
    }, {
        y: "0%",
        rotateX: 0,
        opacity: 1,
        duration: 1,
        stagger: .13,
        ease: "power3.out",
        delay: .2
    })

    ScrollTrigger.saveStyles(".obj-rotate-anim")
    var object_rotates = document.querySelectorAll(".obj-rotate-anim *");

    object_rotates.forEach(object_rotate => {
        gsap.set(object_rotate, { perspective: 400 });
        let tl = gsap.timeline({
            defaults: { duration: 1 },
            scrollTrigger: {
                trigger: object_rotate,
            }
        }).fromTo(object_rotate, {
            y: "60%",
            rotateX: "-40deg",
            opacity: 0
        }, {
            y: "0%",
            rotateX: 0,
            opacity: 1,
            duration: 1,
            stagger: .13,
            ease: "power3.out",
            delay: .2
        })
    });


    var ease_in_elements = document.querySelectorAll(".ease-anim");

    ease_in_elements.forEach(ease_in_elem => {
        let tl = gsap.timeline({
            defaults: { duration: 1 },
            scrollTrigger: {
                trigger: ease_in_elem,
            }
        }).fromTo(ease_in_elem, {
            opacity: 0
        }, {
            opacity: 1,
            duration: 1,
            stagger: .13,
            ease: "power3.out",
            delay: .2
        })
    });


    var clip_y_elements = document.querySelectorAll(".clip-y-anim");

    clip_y_elements.forEach(clip_y_elem => {
        let tl = gsap.timeline({
            defaults: { duration: 1 },
            scrollTrigger: {
                trigger: clip_y_elem,
            }
        }).fromTo(clip_y_elem, {
            'clip-path': "polygon(0 100%, 100% 100%, 100% 100%, 0% 100%)",
            opacity: 0,
            y: "-15%"
        }, {
            'clip-path': "polygon(0% 100%, 100% 100%, 100% 0%, 0% 0%)",
            opacity: 1,
            y: "0%",
            duration: .8,
            stagger: .13,
            ease: "power3.out",
            delay: .2
        })
    });

    // if the browser is safari

    var angle_rotate_els = document.querySelectorAll(".angle-rotate");

    var angle_rotate_alt_els = document.querySelectorAll(".angle-rotate-alt");

    if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
            // add style: transform: translateZ(-1000px); to all elements with class: .agle-rotate and .angle-rotate-alt
            angle_rotate_els.forEach(angle_rotate_el => {
                angle_rotate_el.style.transform = "translateZ(-1000px)";
            }
            );
            angle_rotate_alt_els.forEach(angle_rotate_alt_el => {
                angle_rotate_alt_el.style.transform = "translateZ(-1000px)";
            }
            );

    } else {


        angle_rotate_els.forEach(angle_rotate_el => {
            let tl = gsap.timeline({
                defaults: { duration: 1 },
                scrollTrigger: {
                    trigger: angle_rotate_el,
                    scrub: true,
                }
            }).fromTo(angle_rotate_el, {
                rotateY: 0,
                rotateX: 0,
            }, {
                rotateY: "-12deg",
                rotateX: "4.13deg",
                duration: 1,
                stagger: .13,
                ease: "power3.out",
                delay: .2
            })
        });

        angle_rotate_alt_els.forEach(angle_rotate_alt_el => {
            let tl = gsap.timeline({
                defaults: { duration: 1 },
                scrollTrigger: {
                    trigger: angle_rotate_alt_el,
                    scrub: true,
                }
            }).fromTo(angle_rotate_alt_el, {
                rotateY: 0,
                rotateX: 0,
            }, {
                rotateY: "12deg",
                rotateX: "4.13deg",
                duration: 1,
                stagger: .13,
                ease: "power3.out",
                delay: .2
            })
        });
    }
});