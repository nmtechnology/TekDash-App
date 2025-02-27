<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GroqController;
use App\Http\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/api/groq/query', [GroqController::class, 'query']);
Route::post('groq/query', [GroqController::class, 'query'])->name('groq.query');

Route::get('show-events', [CalendarController::class, 'showEvents']);
Route::post('add-event', [CalendarController::class, 'addEvent']);
Route::post('update-event', [CalendarController::class, 'updateEvent']);
Route::post('delete-event', [CalendarController::class, 'deleteEvent']);
Route::post('add-reminder', [CalendarController::class, 'addReminder']);
Route::post('update-reminder', [CalendarController::class, 'updateReminder']);
Route::post('delete-reminder', [CalendarController::class, 'deleteReminder']);
Route::post('add-note', [CalendarController::class, 'addNote']);
Route::post('update-note', [CalendarController::class, 'updateNote']);
Route::post('delete-note', [CalendarController::class, 'deleteNote']);
Route::post('add-task', [CalendarController::class, 'addTask']);
Route::post('update-task', [CalendarController::class, 'updateTask']);
Route::post('delete-task', [CalendarController::class, 'deleteTask']);
Route::post('add-appointment', [CalendarController::class, 'addAppointment']);
Route::post('update-appointment', [CalendarController::class, 'updateAppointment']);
Route::post('delete-appointment', [CalendarController::class, 'deleteAppointment']);
Route::post('add-meeting', [CalendarController::class, 'addMeeting']);
Route::post('update-meeting', [CalendarController::class, 'updateMeeting']);
Route::post('delete-meeting', [CalendarController::class, 'deleteMeeting']);
Route::post('add-event-attendee', [CalendarController::class, 'addEventAttendee']);
Route::post('update-event-attendee', [CalendarController::class, 'updateEventAttendee']);
Route::post('delete-event-attendee', [CalendarController::class, 'deleteEventAttendee']);
Route::post('add-reminder-attendee', [CalendarController::class, 'addReminderAttendee']);
Route::post('update-reminder-attendee', [CalendarController::class, 'updateReminderAttendee']);
Route::post('delete-reminder-attendee', [CalendarController::class, 'deleteReminderAttendee']);
Route::post('add-note-attendee', [CalendarController::class, 'addNoteAttendee']);
Route::post('update-note-attendee', [CalendarController::class, 'updateNoteAttendee']);
Route::post('delete-note-attendee', [CalendarController::class, 'deleteNoteAttendee']);
Route::post('add-task-attendee', [CalendarController::class, 'addTaskAttendee']);
Route::post('update-task-attendee', [CalendarController::class, 'updateTaskAttendee']);
Route::post('delete-task-attendee', [CalendarController::class, 'deleteTaskAttendee']);
Route::post('add-appointment-attendee', [CalendarController::class, 'addAppointmentAttendee']);
Route::post('update-appointment-attendee', [CalendarController::class, 'updateAppointmentAttendee']);
Route::post('delete-appointment-attendee', [CalendarController::class, 'deleteAppointmentAttendee']);
Route::post('add-meeting-attendee', [CalendarController::class, 'addMeetingAttendee']);
Route::post('update-meeting-attendee', [CalendarController::class, 'updateMeetingAttendee']);
Route::post('delete-meeting-attendee', [CalendarController::class, 'deleteMeetingAttendee']);
Route::post('add-event-attachment', [CalendarController::class, 'addEventAttachment']);
Route::post('update-event-attachment', [CalendarController::class, 'updateEventAttachment']);
Route::post('delete-event-attachment', [CalendarController::class, 'deleteEventAttachment']);
Route::post('add-reminder-attachment', [CalendarController::class, 'addReminderAttachment']);
Route::post('update-reminder-attachment', [CalendarController::class, 'updateReminderAttachment']);
Route::post('delete-reminder-attachment', [CalendarController::class, 'deleteReminderAttachment']);
Route::post('add-note-attachment', [CalendarController::class, 'addNoteAttachment']);
Route::post('update-note-attachment', [CalendarController::class, 'updateNoteAttachment']);
Route::post('delete-note-attachment', [CalendarController::class, 'deleteNoteAttachment']);
Route::post('add-task-attachment', [CalendarController::class, 'addTaskAttachment']);
Route::post('update-task-attachment', [CalendarController::class, 'updateTaskAttachment']);
Route::post('delete-task-attachment', [CalendarController::class, 'deleteTaskAttachment']);
Route::post('add-appointment-attachment', [CalendarController::class, 'addAppointmentAttachment']);
Route::post('update-appointment-attachment', [CalendarController::class, 'updateAppointmentAttachment']);
Route::post('delete-appointment-attachment', [CalendarController::class, 'deleteAppointmentAttachment']);
Route::post('add-meeting-attachment', [CalendarController::class, 'addMeetingAttachment']);
Route::post('update-meeting-attachment', [CalendarController::class, 'updateMeetingAttachment']);
Route::post('delete-meeting-attachment', [CalendarController::class, 'deleteMeetingAttachment']);
Route::post('add-event-attendee-attachment', [CalendarController::class, 'addEventAttendeeAttachment']);
Route::post('update-event-attendee-attachment', [CalendarController::class, 'updateEventAttendeeAttachment']);
Route::post('delete-event-attendee-attachment', [CalendarController::class, 'deleteEventAttendeeAttachment']);
Route::post('add-reminder-attendee-attachment', [CalendarController::class, 'addReminderAttendeeAttachment']);
Route::post('update-reminder-attendee-attachment', [CalendarController::class, 'updateReminderAttendeeAttachment']);
Route::post('delete-reminder-attendee-attachment', [CalendarController::class, 'deleteReminderAttendeeAttachment']);
Route::post('add-note-attendee-attachment', [CalendarController::class, 'addNoteAttendeeAttachment']);
Route::post('update-note-attendee-attachment', [CalendarController::class, 'updateNoteAttendeeAttachment']);
Route::post('delete-note-attendee-attachment', [CalendarController::class, 'deleteNoteAttendeeAttachment']);
Route::post('add-task-attendee-attachment', [CalendarController::class, 'addTaskAttendeeAttachment']);
Route::post('update-task-attendee-attachment', [CalendarController::class, 'updateTaskAttendeeAttachment']);
Route::post('delete-task-attendee-attachment', [CalendarController::class, 'deleteTaskAttendeeAttachment']);
Route::post('add-appointment-attendee-attachment', [CalendarController::class, 'addAppointmentAttendeeAttachment']);
Route::post('update-appointment-attendee-attachment', [CalendarController::class, 'updateAppointmentAttendeeAttachment']);
Route::post('delete-appointment-attendee-attachment', [CalendarController::class, 'deleteAppointmentAttendeeAttachment']);
Route::post('add-meeting-attendee-attachment', [CalendarController::class, 'addMeetingAttendeeAttachment']);    
Route::post('update-meeting-attendee-attachment', [CalendarController::class, 'updateMeetingAttendeeAttachment']);
Route::post('delete-meeting-attendee-attachment', [CalendarController::class, 'deleteMeetingAttendeeAttachment']);
Route::post('add-event-attendee-attachment-attendee', [CalendarController::class, 'addEventAttendeeAttachmentAttendee']);
Route::post('update-event-attendee-attachment-attendee', [CalendarController::class, 'updateEventAttendeeAttachmentAttendee']);
Route::post('delete-event-attendee-attachment-attendee', [CalendarController::class, 'deleteEventAttendeeAttachmentAttendee']);
Route::post('add-reminder-attendee-attachment-attendee', [CalendarController::class, 'addReminderAttendeeAttachmentAttendee']);
Route::post('update-reminder-attendee-attachment-attendee', [CalendarController::class, 'updateReminderAttendeeAttachmentAttendee']);
Route::post('delete-reminder-attendee-attachment-attendee', [CalendarController::class, 'deleteReminderAttendeeAttachmentAttendee']);
Route::post('add-note-attendee-attachment-attendee', [CalendarController::class, 'addNoteAttendeeAttachmentAttendee']);
Route::post('update-note-attendee-attachment-attendee', [CalendarController::class, 'updateNoteAttendeeAttachmentAttendee']);
Route::post('delete-note-attendee-attachment-attendee', [CalendarController::class, 'deleteNoteAttendeeAttachmentAttendee']);
Route::post('add-task-attendee-attachment-attendee', [CalendarController::class, 'addTaskAttendeeAttachmentAttendee']);
Route::post('update-task-attendee-attachment-attendee', [CalendarController::class, 'updateTaskAttendeeAttachmentAttendee']);
// Add this route for the calendar
Route::get('/work-orders', [WorkOrderController::class, 'getWorkOrdersForCalendar']);
Route::get('/work-orders/{id}', [WorkOrderController::class, 'getWorkOrder']);
Route::post('/work-orders', [WorkOrderController::class, 'createWorkOrder']);
Route::put('/work-orders/{id}', [WorkOrderController::class, 'updateWorkOrder']);
Route::delete('/work-orders/{id}', [WorkOrderController::class, 'deleteWorkOrder']);
Route::get('/work-orders/{id}/notes', [WorkOrderController::class, 'getNotes']);
Route::post('/work-orders/{id}/notes', [WorkOrderController::class, 'addNote']);
Route::get('/work-orders/{id}/images', [WorkOrderController::class, 'getImages']);
Route::post('/work-orders/{id}/images', [WorkOrderController::class, 'addImage']);
Route::delete('/work-orders/{id}/images/{imageId}', [WorkOrderController::class, 'deleteImage']);
Route::get('/work-orders/{id}/file-attachments', [WorkOrderController::class, 'getFileAttachments']);
Route::post('/work-orders/{id}/file-attachments', [WorkOrderController::class, 'addFileAttachment']);
Route::delete('/work-orders/{id}/file-attachments/{fileAttachmentId}', [WorkOrderController::class, 'deleteFileAttachment']);
Route::get('/work-orders/{id}/notes', [WorkOrderController::class, 'getNotes']);
Route::post('/work-orders/{id}/notes', [WorkOrderController::class, 'addNote']);
Route::delete('/work-orders/{id}/notes/{noteId}', [WorkOrderController::class, 'deleteNote']);

Route::middleware('auth:sanctum')->get('/users', [UserController::class, 'index']);

// routes/api.php
Route::middleware('auth:sanctum')->get('/users', function () {
    return App\Models\User::select('id', 'name', 'email')->get();
});

Route::middleware('auth:sanctum')->get('/work-orders', function () {
    return WorkOrder::select('id', 'title', 'description', 'date_time as start', 'status', 'user_id', 'customer_id')
                   ->orderBy('date_time', 'asc')
                   ->get();
});
