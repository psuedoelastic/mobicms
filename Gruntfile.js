/*!
 * mobiCMS http://mobicms.net
 */

module.exports = function (grunt) {
    require('time-grunt')(grunt);
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        ////////////////////////////////////////////////////////////
        // Компилируем LESS файлы                                 //
        ////////////////////////////////////////////////////////////
        less: {
            mobicms_thundercloud: {
                files: {
                    'themes/thundercloud/css/mobicms.css': '_sources/mobicms/thundercloud/less/mobicms.less',
                    'themes/thundercloud/css/editors/sceditor/theme.css': '_sources/third-party/sceditor/less/thundercloud/sceditor.less',
                    'themes/thundercloud/css/editors/sceditor/editor.css': '_sources/third-party/sceditor/less/thundercloud/editor.less',
                    'themes/thundercloud/css/editors/codemirror/theme.css': '_sources/third-party/codemirror/less/thundercloud/codemirror.less'
                }
            }
        },

        ////////////////////////////////////////////////////////////
        // Обрабатываем CSS префиксы вендоров                     //
        ////////////////////////////////////////////////////////////
        autoprefixer: {
            mobicms_thundercloud: {
                files: {
                    'themes/thundercloud/css/mobicms.css': 'themes/thundercloud/css/mobicms.css',
                    'themes/thundercloud/css/editors/sceditor/theme.css': 'themes/thundercloud/css/editors/sceditor/theme.css',
                    'themes/thundercloud/css/editors/sceditor/editor.css': 'themes/thundercloud/css/editors/sceditor/editor.css',
                    'themes/thundercloud/css/editors/codemirror/theme.css': 'themes/thundercloud/css/editors/codemirror/theme.css'
                }
            }
        },

        ////////////////////////////////////////////////////////////
        // Минимизируем CSS файлы                                 //
        ////////////////////////////////////////////////////////////
        cssmin: {
            mobicms_thundercloud: {
                files: {
                    'themes/thundercloud/css/mobicms.min.css': ['themes/thundercloud/css/mobicms.css'],
                    'themes/thundercloud/css/editors/sceditor/theme.min.css': ['themes/thundercloud/css/editors/sceditor/theme.css'],
                    'themes/thundercloud/css/editors/sceditor/editor.min.css': ['themes/thundercloud/css/editors/sceditor/editor.css'],
                    'themes/thundercloud/css/editors/codemirror/theme.min.css': ['themes/thundercloud/css/editors/codemirror/theme.css']
                }
            }
        },

        ////////////////////////////////////////////////////////////
        // Собираем и минимизируем JS файл                        //
        ////////////////////////////////////////////////////////////
        uglify: {
            js: {
                options: {
                    warnings: true,
                    compress: true,
                    mangle: true,
                    banner: '/*!\n * Bootstrap v3.3.1 (http://getbootstrap.com) | Copyright 2011-2014 Twitter, Inc. | Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)\n * jQuery Cookie Plugin v1.4.1 (https://github.com/carhartl/jquery-cookie) | Copyright 2013 Klaus Hartl | Released under the MIT license\n * mobiCMS Content Management System (http://mobicms.net) | For copyright and license information, please see the LICENSE.txt\n */\n'
                },

                files: [{
                    src: [
                        '_sources/third-party/jquery.cookie/jquery.cookie.js',
                        '_sources/mobicms/thundercloud/js/toggle.js',
                        '_sources/third-party/bootstrap/js/collapse.js',
                        '_sources/third-party/bootstrap/js/dropdown.js',
                        '_sources/third-party/bootstrap/js/transition.js'
                    ],
                    dest: 'themes/thundercloud/js/mobicms.min.js'
                }]
            }
        },

        ////////////////////////////////////////////////////////////
        // Очищаем папки и удаляем файлы                          //
        ////////////////////////////////////////////////////////////
        clean: {
            assets: [
                'themes/thundercloud/fonts/*',
                'themes/thundercloud/img/*'
            ],

            dist: ['dist/'],

            dist_prepare: [
                'dist/system/cache/system/*',
                'dist/system/logs/*',
                'dist/system/config/system/database.php',
                'dist/system/config/system/settings.php'
            ]
        },

        ////////////////////////////////////////////////////////////
        // Копируем файлы из исходников                           //
        ////////////////////////////////////////////////////////////
        copy: {
            assets: {
                files: [
                    {
                        expand: true,
                        flatten: true,
                        src: ['_sources/mobicms/thundercloud/fonts/**'],
                        dest: 'themes/thundercloud/fonts/',
                        filter: 'isFile'
                    },
                    {
                        expand: true,
                        flatten: true,
                        src: ['_sources/mobicms/thundercloud/img/**'],
                        dest: 'themes/thundercloud/img/',
                        filter: 'isFile'
                    }
                ]
            },

            dist: {
                files: [
                    {
                        dot: true,
                        src: ['assets/**'],
                        dest: 'dist/'
                    },
                    {
                        dot: true,
                        src: ['install/**'],
                        dest: 'dist/'
                    },
                    {
                        dot: true,
                        src: ['modules/**'],
                        dest: 'dist/'
                    },
                    {
                        dot: true,
                        src: ['system/**'],
                        dest: 'dist/'
                    },
                    {
                        dot: true,
                        src: ['themes/**'],
                        dest: 'dist/'
                    },
                    {
                        src: ['.htaccess'],
                        dest: 'dist/'
                    },
                    {
                        src: ['CHANGELOG.txt'],
                        dest: 'dist/'
                    },
                    {
                        src: ['LICENSE.txt'],
                        dest: 'dist/'
                    }, {
                        src: ['README.md'],
                        dest: 'dist/'
                    },
                    {
                        src: ['robots.txt'],
                        dest: 'dist/'
                    },
                    {
                        src: ['index.php'],
                        dest: 'dist/'
                    }
                ]
            }
        },

        ////////////////////////////////////////////////////////////
        // Сжимаем файлы                                          //
        ////////////////////////////////////////////////////////////
        compress: {
            mobicms_thundercloud: {
                options: {
                    mode: 'gzip'
                },

                files: [
                    {
                        src: ['themes/thundercloud/css/mobicms.min.css'],
                        dest: 'themes/thundercloud/css/mobicms.min.css.gz'
                    },
                    {
                        src: ['themes/thundercloud/css/editors/sceditor/theme.min.css'],
                        dest: 'themes/thundercloud/css/editors/sceditor/theme.min.css.gz'
                    },
                    {
                        src: ['themes/thundercloud/css/editors/sceditor/editor.min.css'],
                        dest: 'themes/thundercloud/css/editors/sceditor/editor.min.css.gz'
                    },
                    {
                        src: ['themes/thundercloud/css/editors/codemirror/theme.min.css'],
                        dest: 'themes/thundercloud/css/editors/codemirror/theme.min.css.gz'
                    }
                ]
            },

            js: {
                options: {
                    mode: 'gzip'
                },

                files: [
                    {
                        src: ['themes/thundercloud/js/mobicms.min.js'],
                        dest: 'themes/thundercloud/js/mobicms.min.js.gz'
                    },
                    {
                        src: ['assets/js/sceditor/jquery.sceditor.xhtml.min.js'],
                        dest: 'assets/js/sceditor/jquery.sceditor.xhtml.min.js.gz'
                    }
                ]
            },

            dist: {
                options: {
                    archive: 'distributive/mobicms-<%= pkg.version %>.zip'
                },

                files: [
                    {
                        expand: true,
                        dot: true,
                        cwd: 'dist/',
                        src: ['**']
                    }
                ]
            }
        },

        ////////////////////////////////////////////////////////////
        // Обновляем зависимости                                  //
        ////////////////////////////////////////////////////////////
        devUpdate: {
            main: {
                options: {
                    updateType: 'force',
                    semver: false
                }
            }
        }
    });

    ////////////////////////////////////////////////////////////
    // Загружаем нужные модули                                //
    ////////////////////////////////////////////////////////////
    grunt.loadNpmTasks('grunt-autoprefixer');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-dev-update');

    ////////////////////////////////////////////////////////////////////////////////
    // Описываем наши действия (задания)                                          //
    //                                                                            //
    // default             - выполняются все задачи и готовится дистрибутив       //
    // dist                - готовится дистрибутив                                //
    // js                  - подготавливаются все JS файлы                        //
    // less_thundercloud   - компилируются LESS файлы для темы ThunderCloud       //
    ////////////////////////////////////////////////////////////////////////////////
    grunt.registerTask('default', [
        'less_thundercloud',
        'js'
    ]);

    grunt.registerTask('js', [
        'uglify:js',
        'compress:js'
    ]);

    grunt.registerTask('less_thundercloud', [
        'less:mobicms_thundercloud',
        'autoprefixer:mobicms_thundercloud',
        'cssmin:mobicms_thundercloud',
        'compress:mobicms_thundercloud'
    ]);

    grunt.registerTask('upd', [
        'devUpdate:main'
    ]);
};