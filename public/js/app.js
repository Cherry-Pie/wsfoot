'use strict';

var App = 
{
    config: {},
    ws: null,
    user: null,
    section: null,
    seat: null,
    reserveButton: null,
    countdown: null,

    init: function() 
    {
        App.initSocketConnection();
        App.initSectionInfoModal();
        App.initModal();
        App.initReserveButton();
    }, // end init
    
    initReserveButton: function()
    {
        App.reserveButton = $('#reserve-btn');
        App.reserveButton.on('click', function() {
            var seconds = 5;
            App.reserveButton.text(seconds);
            App.countdown = setInterval(function() {
                seconds--;
                App.reserveButton.text(seconds);
                if (!seconds) {
                    clearInterval(App.countdown);
                    
                    App.reserve('disable');
                    App.reserveButton.text('ok fine');
                    
                    App.ws.send(JSON.stringify({
                        action: 'seat_reserve', 
                        info: {
                            ident: App.seat,
                            section: App.section,
                            user: App.user
                        }
                    }));
                    
                    App.seat = null;
                    $('.seat.active', '#modal')
                        .removeClass('available')
                        .addClass('occupied')
                        .removeClass('active')
                        .removeClass('in-process');
                }
            }, 1000);
        });
    }, // end initReserveButton

    initModal: function()
    {
        $('.modal-close', '#modal').on('click', function() {
            App.onSeatLeave();
            App.onSectionLeave();
            App.modal('close');
        });
    }, // end initModal

    initSocketConnection: function() 
    {
        App.ws = new WebSocket('ws://'+ App.config.socket_address);
        App.ws.onmessage = function(evt) { 
            var data = $.parseJSON(evt.data);
            
            switch(data.action) {
                case 'get_in_process_seats':
                    App.checkInProcessSeats(data.info);
                    break;
                case 'seat_available':
                    App.checkAvailableSeat(data.info);
                    break;
                case 'seat_occupied':
                    App.informAboutOccupiedSeat(data.info);
                    App.changeAvailableSeatsCount(data.info);
                    break;
            }
        };
    }, // end initSocketConnection
    
    changeAvailableSeatsCount: function(info)
    {
        var $totalCount = $('.count', '.all-available-seats');
        $totalCount.text($totalCount.text() - 1);
        
        if (App.section == info.section) {
            var $sectionCount = $('.count', '.section-available-seats');
            $sectionCount.text($sectionCount.text() - 1);
        }
    }, // end changeAvailableSeatsCount
    
    informAboutOccupiedSeat: function(info)
    {
        if (!info.success) {
            return;
        }
        
        if (App.seat == info.seat) {
            App.seat = null;
            clearInterval(App.countdown);
            App.reserve('disable');
            App.reserveButton.text('too late');
        }
        
        $('#ticket-id-'+ info.seat)
            .removeClass('available')
            .addClass('occupied')
            .removeClass('active')
            .removeClass('in-process');
    }, // end informAboutOccupiedSeat

    initSectionInfoModal: function()
    {
        $('.section-modal-anchors').on('click', function(e) {
            e.preventDefault();

            var section = $(this).data('section');
            var data = { section: section };

            $.post('/get-section-info', data, function(response) {
                App.onSectionSet(section);
                App.modal('open', {
                    title: 'Section #'+ section,
                    content: response.html,
                    count: response.count
                });
                App.initSeatsClick();
                
                App.ws.send(JSON.stringify({
                    action: 'get_in_process_seats', 
                    info: {
                        section: App.section
                    }
                }));
            }, "json");
        });
    }, // end initSectionInfoModal
    
    checkInProcessSeats: function(info)
    {
        if (App.section != info.section) {
            return;
        }
        
        $.each(info.ids, function(key, val) {
            $('#ticket-id-'+ val).addClass('in-process');
        });
    }, // end checkInProcessSeats
    
    checkAvailableSeat: function(info)
    {
        if (App.section != info.section) {
            return;
        }
        
        $('#ticket-id-'+ info.id).removeClass('in-process');
    }, // end checkAvailableSeat
    
    initSeatsClick: function()
    {
        var $seats = $('.seat.available', '#modal');
        $seats.on('click', function() {
            var $elem = $(this);
            
            // TODO:
            if ($elem.hasClass('active')) {
                App.reserve('disable');
                App.onSeatLeave();
                
                $elem.removeClass('active');
            } else {
                App.onSeatLeave();
                App.onSeatSet($elem.data('id'));
                App.reserve('enable');
                
                $seats.removeClass('active');
                $elem.addClass('active');
            }
        });
    }, // end initSeatsClick

    modal: function(action, options)
    {
        var $modal = $('#modal');
        options = $.extend({
            title:   '',
            content: '',
            count:   0
        }, options);

        if (action === 'open') {
            App.reserve('disable');
            $modal.find('.modal-title').text(options.title);
            $modal.find('.count').text(options.count);
            $modal.find('.modal-content').html(options.content);
            $modal.show();
        } else if (action === 'close') {
            App.onSectionLeave();
            App.onSeatLeave();
            $modal.hide();
        }
    }, // end modal
    
    onSectionSet: function(section)
    {
        App.section = section;
    }, // end onSectionSet
    
    onSectionLeave: function()
    {
        App.section = null;
    }, // end onSectionLeave
    
    onSeatSet: function(id)
    {
        App.seat = id;
        
        App.ws.send(JSON.stringify({
            action: 'seat_set', 
            info: {
                ident: id,
                section: App.section,
                user: App.user
            }
        }));
    }, // end onSeatSet
    
    onSeatLeave: function()
    {
        App.ws.send(JSON.stringify({
            action: 'seat_leave', 
            info: {
                ident: App.seat,
                section: App.section,
                user: App.user
            }
        }));
        
        App.seat = null;
    }, // end onSeatLeave
    
    reserve: function(action)
    {
        if (action == 'enable') {
            App.reserveButton.attr('disabled', false);
        } else if (action == 'disable') {
            App.reserveButton.attr('disabled', true);
        }
    }, // end reserveButton
    
};

$(document).ready(function() {
    App.init();
});

