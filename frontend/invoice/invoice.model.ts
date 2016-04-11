import {Item} from "./item.model";
import {CustomerModel} from "./customer.model";

export class Invoice {
    public id:number;
    public customer:CustomerModel;
    public referenceNumber:number;
    public invoiceNumber:number;
    public created:string;
    public due:string;
    public items:Array<Item> = [];

    public total():number {
        var total = 0;

        for (var i in this.items) {
            total += this.items[i].total();
        }

        return total;
    }

    public addItem():void {
        this.items.push(new Item());
    }
}
