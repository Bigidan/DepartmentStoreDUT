import {Component, Input, OnChanges, SimpleChanges} from '@angular/core';
import {NgClass, NgForOf, NgIf} from "@angular/common";
import {DataService} from "../../services/data.service";
import { HttpClientModule } from '@angular/common/http';
import {MatIcon} from "@angular/material/icon";
import {MatTab, MatTabGroup} from "@angular/material/tabs";
import {MatAccordion, MatExpansionPanel, MatExpansionPanelHeader} from "@angular/material/expansion";
import { MatExpansionPanelTitle, MatExpansionPanelDescription } from "@angular/material/expansion";

@Component({
  selector: 'app-details',
  standalone: true,
    imports: [
        HttpClientModule,
        NgIf,
        MatIcon,
        MatTabGroup,
        MatTab,
        MatAccordion,
        MatExpansionPanel,
        MatExpansionPanelHeader,
        MatExpansionPanelTitle,
        MatExpansionPanelDescription,
        NgClass,
        NgForOf,
    ],
  templateUrl: "details.component.html",
  styleUrl: "details.component.css",
  providers: [DataService],
})
export class DetailsComponent implements OnChanges {
  @Input() selectedItem: any;
  @Input() selectedNavItem: any;
  details: any;
  item: any;
  provider: any;

  constructor(private dataService: DataService) {}

  ngOnChanges(changes: SimpleChanges) {
    if (changes['selectedItem']) {
      this.onSelectedItemChange(changes['selectedItem'].currentValue);
    }
  }

  getData(selectedNavItem: string, selectedItemId: string) {
    this.dataService.getItemData(selectedNavItem, selectedItemId).subscribe(data => {
        this.details = data.details;
        this.item = data.items;
        this.provider = data.provider;

      console.log(this.details);
    });
  }

  onSelectedItemChange(newItem: any) {
    // Виконати необхідні дії при зміні selectedItem
    console.log('Selected item changed to:', newItem, this.selectedNavItem);
    this.getData(this.selectedNavItem, newItem.id);
  }
}
