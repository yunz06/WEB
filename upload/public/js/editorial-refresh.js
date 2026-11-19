/* Editorial image replacement and attribution only. Does not touch forms, routes or app state. */
(() => {
    'use strict';

    const assets = {
        'seed_world.jpg': {
            url: 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Skyline_of_Shenzhen.jpg/1280px-Skyline_of_Shenzhen.jpg',
            alt: 'Đường chân trời Thâm Quyến nhìn từ công viên Shenzhen Bay',
            credit: 'Wishds',
            page: 'https://commons.wikimedia.org/wiki/File:Skyline_of_Shenzhen.jpg',
            license: 'CC BY-SA 3.0',
            licenseUrl: 'https://creativecommons.org/licenses/by-sa/3.0/',
            caption: 'Thâm Quyến — nơi diễn ra Hội nghị Cấp cao APEC 2026.'
        },
        'seed_business.jpg': {
            url: 'https://upload.wikimedia.org/wikipedia/commons/8/8f/Rice_fields_in_Vietnam.jpg',
            alt: 'Ruộng lúa xanh tại Việt Nam',
            credit: 'Chris Feser',
            page: 'https://commons.wikimedia.org/wiki/File:Rice_fields_in_Vietnam.jpg',
            license: 'CC BY 2.0',
            licenseUrl: 'https://creativecommons.org/licenses/by/2.0/',
            caption: 'Ruộng lúa tại Việt Nam — ảnh minh họa cho chủ đề thương mại gạo.'
        },
        'seed_technology.jpg': {
            url: 'https://upload.wikimedia.org/wikipedia/commons/d/d1/Data_Center.jpg',
            alt: 'Không gian bên trong trung tâm dữ liệu với các tủ máy chủ',
            credit: 'Gideonwills44',
            page: 'https://commons.wikimedia.org/wiki/File:Data_Center.jpg',
            license: 'CC BY-SA 4.0',
            licenseUrl: 'https://creativecommons.org/licenses/by-sa/4.0/',
            caption: 'Hạ tầng trung tâm dữ liệu — ảnh minh họa cho lĩnh vực công nghệ số.'
        },
        'seed_work.jpg': {
            url: 'https://upload.wikimedia.org/wikipedia/commons/7/7f/Modern_office_workspace_featuring_a_computer.jpg',
            alt: 'Không gian làm việc hiện đại với bàn và máy tính',
            credit: 'Shixart1985',
            page: 'https://commons.wikimedia.org/wiki/File:Modern_office_workspace_featuring_a_computer.jpg',
            license: 'CC BY 2.0',
            licenseUrl: 'https://creativecommons.org/licenses/by/2.0/',
            caption: 'Không gian làm việc với máy tính — ảnh minh họa cho chủ đề AI và việc làm.'
        },
        'seed_sports.jpg': {
            url: 'https://upload.wikimedia.org/wikipedia/commons/9/9d/Youth_Football_Stadium.jpg',
            alt: 'Sân vận động bóng đá trẻ tại Thanh Đảo, Trung Quốc',
            credit: 'K.Y.K.Z.K.',
            page: 'https://commons.wikimedia.org/wiki/File:Youth_Football_Stadium.jpg',
            license: 'CC BY-SA 4.0',
            licenseUrl: 'https://creativecommons.org/licenses/by-sa/4.0/',
            caption: 'Sân bóng đá trẻ tại Thanh Đảo — ảnh minh họa, không phải ảnh trận U17 World Cup.'
        },
        'seed_education.jpg': {
            url: 'https://upload.wikimedia.org/wikipedia/commons/8/8e/Graduated_students.JPG',
            alt: 'Sinh viên tốt nghiệp Đại học Y Hải Phòng trong lễ trao bằng',
            credit: 'Khuepm',
            page: 'https://commons.wikimedia.org/wiki/File:Graduated_students.JPG',
            license: 'CC BY-SA 3.0',
            licenseUrl: 'https://creativecommons.org/licenses/by-sa/3.0/',
            caption: 'Sinh viên tốt nghiệp Đại học Y Hải Phòng — ảnh minh họa, không phải lễ vinh danh tại Hà Nội.'
        },
        'seed_lifestyle.jpg': {
            url: 'https://upload.wikimedia.org/wikipedia/commons/a/a5/Hanoi_Skyline.jpg',
            alt: 'Toàn cảnh đường chân trời Hà Nội',
            credit: 'Minhvuong308hn',
            page: 'https://commons.wikimedia.org/wiki/File:Hanoi_Skyline.jpg',
            license: 'CC BY-SA 4.0',
            licenseUrl: 'https://creativecommons.org/licenses/by-sa/4.0/',
            caption: 'Toàn cảnh Hà Nội — ảnh minh họa bối cảnh đô thị, không phải ảnh cuộc gặp ngoại giao.'
        },
        'seed_energy.jpg': {
            url: 'https://upload.wikimedia.org/wikipedia/commons/8/8a/Gas_Turbine_Power_Plant.JPG',
            alt: 'Nhà máy điện tuabin khí với các tổ máy và đường ống kỹ thuật',
            credit: 'Pro-Per Energy Services',
            page: 'https://commons.wikimedia.org/wiki/File:Gas_Turbine_Power_Plant.JPG',
            license: 'CC BY-SA 4.0',
            licenseUrl: 'https://creativecommons.org/licenses/by-sa/4.0/',
            caption: 'Nhà máy điện tuabin khí — ảnh minh họa, không phải cơ sở thuộc thỏa thuận Việt Nam–Nga.'
        }
    };

    const makeCredit = (asset) => {
        const line = document.createElement('span');
        line.className = 'editorial-photo-credit';
        line.append(document.createTextNode(`${asset.caption} Ảnh: ${asset.credit} · `));
        const source = document.createElement('a');
        source.href = asset.page;
        source.target = '_blank';
        source.rel = 'noopener noreferrer';
        source.textContent = 'Wikimedia Commons';
        line.append(source, document.createTextNode(' · '));
        const license = document.createElement('a');
        license.href = asset.licenseUrl;
        license.target = '_blank';
        license.rel = 'noopener noreferrer';
        license.textContent = asset.license;
        line.append(license);
        return line;
    };

    const addCaption = (img, asset) => {
        if (img.dataset.editorialCreditAdded === '1') return;
        img.dataset.editorialCreditAdded = '1';
        const figure = img.closest('.article-detail__hero');
        if (figure) {
            const caption = document.createElement('figcaption');
            caption.className = 'editorial-photo-credit';
            caption.append(makeCredit(asset));
            figure.append(caption);
            return;
        }
        const card = img.closest('.latest-story');
        if (card) {
            const body = card.querySelector('.latest-story__body');
            (body || card).append(makeCredit(asset));
            return;
        }
        const visual = img.closest('.featured-story__visual');
        if (visual) visual.append(makeCredit(asset));
    };

    const applyEditorialImages = () => {
        document.querySelectorAll('img[src*="/public/uploads/articles/"]').forEach((img) => {
            let fileName = '';
            try {
                fileName = decodeURIComponent(new URL(img.getAttribute('src'), window.location.href).pathname.split('/').pop());
            } catch (_) {
                return;
            }
            const asset = assets[fileName];
            if (!asset || img.dataset.editorialImageApplied === '1') return;
            img.dataset.editorialImageApplied = '1';
            img.src = asset.url;
            img.alt = asset.alt;
            img.loading = img.closest('.featured-story__visual') ? 'eager' : 'lazy';
            img.referrerPolicy = 'no-referrer';
            addCaption(img, asset);
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyEditorialImages, { once: true });
    } else {
        applyEditorialImages();
    }
})();
