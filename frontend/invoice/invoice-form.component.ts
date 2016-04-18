
import {Component} from 'angular2/core';
import {Invoice} from "./invoice.model";
import {Input} from "angular2/core";

@Component({
    selector: 'invoice-form',
    templateUrl: 'app/invoice/form/invoice.html'
})
export class InvoiceFormComponent {
    @Input() invoice:Invoice;
}
