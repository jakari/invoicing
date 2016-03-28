import {Item} from "./item.model";

export class Invoice {
    public id:number;
    public customer:string;
    public customerAddress:string;
    public referenceNumber:number;
    public invoiceNumber:number;
    public created:Date;
    public due:Date;
    public items:Array<Item>;

    public total():number {
        var total = 0;

        for (var i in this.items) {
            total += this.items[i].total();
        }

        return total;
    }
}
